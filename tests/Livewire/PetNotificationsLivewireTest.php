<?php

use App\Http\Livewire\Pet\Notifications;
use App\Models\Pet;
use App\Models\PetNotification;
use App\Models\User;

use function Pest\Laravel\actingAs;

// Livewire specific tests focus on interactive methods exposed by the component.
it('marks all notifications as read and resets the unread counter', function (): void {
    // Prepare an owner with a pet and two unread notifications to simulate a busy inbox.
    $owner = User::factory()->create();
    $pet = Pet::factory()->create(['user_id' => $owner->id]);

    PetNotification::create([
        'pet_id' => $pet->id,
        'sender_pet_id' => null,
        'type' => 'friend_request',
        'content' => 'sent you a friend request',
        'data' => ['action' => 'friend_request'],
    ]);

    PetNotification::create([
        'pet_id' => $pet->id,
        'sender_pet_id' => null,
        'type' => 'activity',
        'content' => 'logged a walk',
        'data' => ['action' => 'activity', 'activity_id' => 5],
    ]);

    actingAs($owner);

    // Trigger the bulk read action and assert the database and component state both update.
    $component = app(Notifications::class);
    $component->mount($pet->id);
    $component->markAllAsRead();

    expect($component->unreadCount)->toBe(0);

    expect(PetNotification::whereNull('read_at')->count())->toBe(0);
});
