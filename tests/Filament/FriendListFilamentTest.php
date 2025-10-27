<?php

use App\Http\Livewire\Common\Friend\List as FriendListComponent;
use App\Models\Friendship;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use function Pest\Laravel\actingAs;

/**
 * Confirms the friend list component surfaces predictable data when embedded inside Filament panels.
 */
it('provides predictable view data for Filament admin usage', function (): void {
    // Filament renders Livewire components, so ensure the component resolves cleanly with accepted friendships.
    $owner = User::factory()->create();
    $friend = User::factory()->create([
        'name' => 'Atlas Explorer',
        'username' => 'atlas',
    ]);

    Friendship::create([
        'sender_id' => $owner->id,
        'recipient_id' => $friend->id,
        'status' => Friendship::STATUS_ACCEPTED,
        'category' => 'Adventures',
    ]);

    actingAs($owner);

    Cache::flush();

    $component = app(FriendListComponent::class);
    $component->mount('user', $owner->id);
    $component->page = 1;

    $view = $component->render();

    expect($view->name())->toBe('livewire.common.friend.list');
    $data = $view->getData();

    expect($data['friends']->total())->toBe(1);
    expect($data['friends']->first()->name)->toBe('Atlas Explorer');
    expect($data['categories'])->toBe(['Adventures']);
});
