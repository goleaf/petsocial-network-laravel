<?php

use App\Http\Livewire\Common\Friend\List as FriendListComponent;
use App\Models\Friendship;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;
use function Pest\Laravel\actingAs;

/**
 * Exercises the interactive actions exposed by the friend list Livewire component.
 */
it('toggles select all and removes friends successfully', function (): void {
    // Create an owner alongside two accepted friendships to simulate a populated list.
    $owner = User::factory()->create();
    $friendA = User::factory()->create();
    $friendB = User::factory()->create();

    foreach ([$friendA, $friendB] as $friend) {
        Friendship::create([
            'sender_id' => $owner->id,
            'recipient_id' => $friend->id,
            'status' => Friendship::STATUS_ACCEPTED,
        ]);
    }

    actingAs($owner);

    Cache::flush();

    $component = Livewire::test(FriendListComponent::class, [
        'entityType' => 'user',
        'entityId' => $owner->id,
    ]);

    $component->set('page', 1);

    // Selecting all should capture every available friend identifier.
    $component->call('toggleSelectAll')
        ->assertSet('selectAll', true);

    expect($component->get('selectedFriends'))
        ->toEqualCanonicalizing([$friendA->id, $friendB->id]);

    // Invoking the toggle again clears the selection entirely.
    $component->call('toggleSelectAll')
        ->assertSet('selectAll', false)
        ->assertSet('selectedFriends', []);

    // Removing a specific friend deletes the friendship record and resets state.
    $component->set('selectedFriends', [$friendA->id]);
    $component->call('removeFriends');

    expect(Friendship::where('sender_id', $owner->id)->count())->toBe(1);
    expect($component->get('selectedFriends'))->toBe([]);
});

/**
 * Confirms categorisation updates persist to storage and emit Livewire events.
 */
it('categorizes selected friends and emits refresh events', function (): void {
    $owner = User::factory()->create();
    $friend = User::factory()->create();

    Friendship::create([
        'sender_id' => $owner->id,
        'recipient_id' => $friend->id,
        'status' => Friendship::STATUS_ACCEPTED,
    ]);

    actingAs($owner);

    Cache::flush();

    $component = Livewire::test(FriendListComponent::class, [
        'entityType' => 'user',
        'entityId' => $owner->id,
    ]);

    $component->set('page', 1);

    $component->set('selectedFriends', [$friend->id]);
    $component->set('newCategory', 'Hiking Crew');

    $component->call('applyCategory')
        ->assertDispatched('friendCategorized')
        ->assertSet('showCategoryModal', false)
        ->assertSet('selectedFriends', [])
        ->assertSet('newCategory', '');

    expect(Friendship::where('sender_id', $owner->id)
        ->where('recipient_id', $friend->id)
        ->value('category'))
        ->toBe('Hiking Crew');
});

/**
 * Ensures the category modal only opens when at least one friend has been selected.
 */
it('requires a selection before showing the category modal', function (): void {
    // Create a profile owner along with a single friend relationship for realistic state.
    $owner = User::factory()->create();
    $friend = User::factory()->create();

    Friendship::create([
        'sender_id' => $owner->id,
        'recipient_id' => $friend->id,
        'status' => Friendship::STATUS_ACCEPTED,
    ]);

    actingAs($owner);

    Cache::flush();

    $component = Livewire::test(FriendListComponent::class, [
        'entityType' => 'user',
        'entityId' => $owner->id,
    ]);

    // Attempting to open the modal without selecting friends should surface the flash error.
    $component->set('selectedFriends', []);
    $component->call('showCategoryModal');

    expect(session()->get('error'))
        ->toBe(__('friends.no_friends_selected'));

    session()->forget('error');

    // Providing a friend selection should flip the modal visibility flag to true.
    $component->set('selectedFriends', [$friend->id]);
    $component->call('showCategoryModal')
        ->assertSet('showCategoryModal', true);
});
