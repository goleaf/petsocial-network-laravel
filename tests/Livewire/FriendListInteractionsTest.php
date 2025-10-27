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
