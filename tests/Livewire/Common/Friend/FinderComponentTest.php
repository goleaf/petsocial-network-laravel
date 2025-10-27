<?php

use App\Models\Friendship;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Tests\Support\Common\Friend\FinderTestHarness;
use function Pest\Laravel\actingAs;

it('filters search results to non-friends and resets import state', function () {
    // Clear caches to guarantee the finder rebuilds the friend list from the database.
    Cache::flush();

    // Authenticate the owner so Livewire can enforce authorization successfully.
    $owner = tap(User::factory()->create(), function (User $user) {
        $user->forceFill(['username' => 'companion_seeker'])->save();
    });
    actingAs($owner);

    // Create an existing friendship that should be excluded from search results.
    $alreadyFriend = tap(User::factory()->create(), function (User $user) {
        $user->forceFill(['username' => 'already_bff'])->save();
    });
    Friendship::create([
        'sender_id' => $owner->id,
        'recipient_id' => $alreadyFriend->id,
        'status' => Friendship::STATUS_ACCEPTED,
    ]);

    // Seed a discoverable account that should surface in the search response.
    $suggestion = tap(User::factory()->create(), function (User $user) {
        $user->forceFill(['username' => 'fresh_friend'])->save();
    });

    // Prime the component with stale import results to verify the watcher clears them.
    $component = Livewire::test(FinderTestHarness::class, [
        'entityType' => 'user',
        'entityId' => $owner->id,
    ])->set('importResults', [['status' => 'outdated']])
      ->set('search', substr($suggestion->name, 0, 3));

    // Inspect the underlying component to validate the computed search collection.
    $results = $component->instance()->getSearchResults();

    // Ensure stale imports were cleared and only the new suggestion remains.
    $component->assertSet('importResults', []);
    expect($results->pluck('id'))
        ->toContain($suggestion->id)
        ->not->toContain($alreadyFriend->id)
        ->and($component->instance()->emittedEvents)->toBe([]);
});

it('sends friend requests and dispatches confirmation events', function () {
    // Reset caches so the friend list reflects each assertion without residue.
    Cache::flush();

    // Authenticate the seeker user so authorization passes when sending the request.
    $seeker = User::factory()->create();
    actingAs($seeker);

    // Seed a candidate that the seeker will invite.
    $candidate = User::factory()->create();

    // Trigger the Livewire action to send a friendship request.
    $component = Livewire::test(FinderTestHarness::class, [
        'entityType' => 'user',
        'entityId' => $seeker->id,
    ])->call('sendFriendRequest', $candidate->id);

    // Validate the persistence layer and Livewire event dispatch.
    expect(
        Friendship::where('sender_id', $seeker->id)
            ->where('recipient_id', $candidate->id)
            ->where('status', Friendship::STATUS_PENDING)
            ->exists()
    )->toBeTrue();
    expect($component->instance()->emittedEvents)->toContain(['friendRequestSent', [$candidate->id]]);
});

it('imports CSV contacts through the Livewire workflow', function () {
    // Reset caches so fresh friendship data powers each assertion path.
    Cache::flush();

    // Authenticate as the member initiating the contact import.
    $seeker = User::factory()->create();
    actingAs($seeker);

    // Seed an accepted friendship to confirm the importer respects existing links.
    $confirmedFriend = User::factory()->create();
    Friendship::create([
        'sender_id' => $seeker->id,
        'recipient_id' => $confirmedFriend->id,
        'status' => Friendship::STATUS_ACCEPTED,
    ]);

    // Seed an additional user who should surface as a fresh discovery in the results.
    $newContact = tap(User::factory()->create(), function (User $user) {
        // Populate the optional phone column so CSV lookups exercise every field.
        $user->forceFill(['phone' => '555-0199'])->save();
    });

    // Compose a realistic CSV payload to upload through the Livewire component.
    $csv = <<<CSV
    name,email,phone
    {$confirmedFriend->name},{$confirmedFriend->email},
    {$newContact->name},{$newContact->email},{$newContact->phone}
    CSV;

    // Fake an uploaded file to drive the Livewire validation and parsing workflow.
    $upload = UploadedFile::fake()->createWithContent('contacts.csv', $csv);

    // Trigger the import routine and capture the resulting component state.
    $component = Livewire::test(FinderTestHarness::class, [
        'entityType' => 'user',
        'entityId' => $seeker->id,
    ])->set('importType', 'csv')
      ->set('importFile', $upload)
      ->call('processImport');

    // Assert the component toggled its loading indicators and exposed the parsed payload.
    $component->assertSet('processingImport', false)
        ->assertSet('importResults.0.status', 'friend')
        ->assertSet('importResults.1.status', 'found');
});
