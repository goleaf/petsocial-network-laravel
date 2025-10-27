<?php

use App\Http\Livewire\Pet\Notifications;
use App\Models\Pet;
use App\Models\PetNotification;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

use function Pest\Laravel\actingAs;

// Livewire specific tests focus on interactive methods exposed by the component.
it('marks all notifications as read and resets the unread counter', function (): void {
    // Hydrate the in-memory database schema so factories can persist records safely.
    prepareTestDatabase();
    preparePetNotificationSchema();

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

// Confirm that the per-notification read action clears the cache and refreshes component state.
it('marks an individual notification as read and refreshes the cached unread count', function (): void {
    // Reset the sqlite memory database to ensure a pristine state for this scenario.
    prepareTestDatabase();
    preparePetNotificationSchema();

    // Reset the cache layer so each assertion observes a deterministic state.
    Cache::flush();

    // Build the owner, their pet, and two unread notifications to exercise the markAsRead path.
    $owner = User::factory()->create();
    $pet = Pet::factory()->create(['user_id' => $owner->id]);
    $firstNotification = PetNotification::create([
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
        'data' => ['action' => 'activity'],
    ]);

    actingAs($owner);

    // Mount the component and prime the unread counter which also caches the current total.
    $component = app(Notifications::class);
    $component->mount($pet->id);
    $component->updateUnreadCount();

    $cacheKey = "pet_{$pet->id}_unread_notifications_count";
    expect(Cache::get($cacheKey))->toBe(2);
    expect($component->unreadCount)->toBe(2);

    // Mark a single notification as read and ensure both cache and state reflect the change.
    $component->markAsRead($firstNotification->id);

    expect(PetNotification::find($firstNotification->id)->read_at)->not()->toBeNull();
    expect(Cache::get($cacheKey))->toBe(1);
    expect($component->unreadCount)->toBe(1);
});
