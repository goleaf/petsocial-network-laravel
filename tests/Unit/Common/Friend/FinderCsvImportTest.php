<?php

use App\Http\Livewire\Common\Friend\Finder;
use App\Models\Friendship;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use function Pest\Laravel\actingAs;

/**
 * Helper component exposes the protected import routines for focused assertions.
 */
class FinderCsvImportTestComponent extends Finder
{
    /**
     * Surface the CSV import routine so the unit test can evaluate its payload.
     */
    public function parseCsv(string $content): array
    {
        // Delegate to the inherited protected logic without altering behaviour.
        return $this->processCsvImport($content);
    }
}

it('parses contact CSV data and flags existing friendships', function () {
    // Flush cached friendship identifiers to keep the assertions deterministic.
    Cache::flush();

    // Authenticate as the seeker so friendship queries and authorization succeed.
    $seeker = User::factory()->create();
    actingAs($seeker);

    // Create a friend relationship that should be marked as already connected.
    $existingFriend = User::factory()->create();
    Friendship::create([
        'sender_id' => $seeker->id,
        'recipient_id' => $existingFriend->id,
        'status' => Friendship::STATUS_ACCEPTED,
    ]);

    // Seed an additional account that should be detected as a new discovery.
    $potentialFriend = tap(User::factory()->create(), function (User $user) {
        // Force fill ensures optional attributes outside the fillable list persist for lookups.
        $user->forceFill(['phone' => '555-1234'])->save();
    });

    // Instantiate the helper component and initialise it with the seeker context.
    $component = new FinderCsvImportTestComponent();
    $component->mount('user', $seeker->id);

    // Compose a CSV payload mirroring the UI export format, including a blank row for skipping.
    $csv = <<<CSV
    name,email,phone
    {$existingFriend->name},{$existingFriend->email},
    {$potentialFriend->name},{$potentialFriend->email},{$potentialFriend->phone}
    ,,
    CSV;

    // Parse the contacts and capture the structured import results.
    $results = $component->parseCsv($csv);

    // Confirm the friend relationship is tagged correctly and new contacts stay discoverable.
    expect($results)
        ->toHaveCount(2)
        ->and($results[0]['status'])->toBe('friend')
        ->and($results[1]['status'])->toBe('found');
});
