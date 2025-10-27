<?php

use App\Http\Livewire\Common\NotificationCenter;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;

// Refresh the database so Livewire interactions touch a consistent notification schema.
uses(RefreshDatabase::class);

it('marks individual notifications as read and refreshes the unread counter', function (): void {
    // Ensure stale cache entries do not leak between Livewire lifecycle assertions.
    Cache::flush();

    // Seed the component with a single unread notification owned by the authenticated user.
    $user = User::factory()->create();
    $notification = UserNotification::factory()
        ->for($user)
        ->create([
            'message' => 'Friend request approved',
            'read_at' => null,
        ]);

    $this->actingAs($user);

    // Exercise the Livewire component, mark the notification as read, and confirm the unread badge resets.
    Livewire::test(NotificationCenter::class, ['entityType' => 'user', 'entityId' => $user->id])
        ->assertSet('unreadCount', 1)
        ->call('markAsRead', $notification->id)
        ->assertSet('unreadCount', 0);

    expect($notification->fresh()->read_at)->not->toBeNull();
});

it('marks all notifications as read when the bulk action is triggered', function (): void {
    // Clear any cached counts before seeding the component with unread entries.
    Cache::flush();

    // Create two unread notifications to verify the bulk mark-as-read workflow.
    $user = User::factory()->create();
    UserNotification::factory()
        ->count(2)
        ->for($user)
        ->create(['read_at' => null]);

    $this->actingAs($user);

    // Trigger the bulk action and confirm both the Livewire state and persisted records are updated.
    Livewire::test(NotificationCenter::class, ['entityType' => 'user', 'entityId' => $user->id])
        ->assertSet('unreadCount', 2)
        ->call('markAllAsRead')
        ->assertSet('unreadCount', 0);

    expect(UserNotification::whereNull('read_at')->count())->toBe(0);
});

it('deletes notifications and refreshes the rendered collection', function (): void {
    // Reset cached totals so the Livewire component recalculates the unread badge.
    Cache::flush();

    // Seed an unread notification to exercise the delete pathway for the authenticated member.
    $user = User::factory()->create();
    $notification = UserNotification::factory()
        ->for($user)
        ->create([
            'message' => 'System maintenance reminder',
            'read_at' => null,
        ]);

    $this->actingAs($user);

    // Remove the notification and ensure the component clears state while the record disappears.
    Livewire::test(NotificationCenter::class, ['entityType' => 'user', 'entityId' => $user->id])
        ->assertSet('unreadCount', 1)
        ->call('delete', $notification->id)
        ->assertSet('unreadCount', 0)
        ->assertViewIs('livewire.common.notification-center')
        ->assertViewHas('notifications', function ($paginator): bool {
            // After deletion the paginator should be empty for the member's feed.
            return $paginator->total() === 0;
        });

    expect(UserNotification::count())->toBe(0);
});
