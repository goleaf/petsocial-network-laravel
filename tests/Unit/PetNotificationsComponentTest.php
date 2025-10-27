<?php

namespace Tests\Unit;

use App\Http\Livewire\Pet\Notifications;
use App\Models\Pet;
use App\Models\PetNotification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * Unit level assertions target the component's caching mechanics directly.
 */
class PetNotificationsComponentTest extends TestCase
{
    use RefreshDatabase;

    public function test_update_unread_count_caches_the_current_total(): void
    {
        // Prime the database with an owner, their pet, and a single unread notification.
        if (! file_exists(database_path('testing.sqlite'))) {
            // Ensure the sqlite database file exists because RefreshDatabase targets it by default.
            touch(database_path('testing.sqlite'));
        }

        $owner = User::factory()->create();
        $pet = Pet::factory()->create(['user_id' => $owner->id]);
        PetNotification::create([
            'pet_id' => $pet->id,
            'sender_pet_id' => null,
            'type' => 'friend_request',
            'content' => 'sent you a friend request',
            'data' => ['action' => 'friend_request'],
        ]);

        $this->actingAs($owner);

        // Reset the cache so we can assert the component writes the expected value.
        Cache::flush();
        $component = app(Notifications::class);
        $component->mount($pet->id);

        $cacheKey = "pet_{$pet->id}_unread_notifications_count";

        // Remove any cached value from the mount cycle before running the assertion target.
        Cache::forget($cacheKey);
        $component->updateUnreadCount();

        // The unread count should be memoized for five minutes and reflected on the component instance.
        $this->assertTrue(Cache::has($cacheKey));
        $this->assertSame(1, Cache::get($cacheKey));
        $this->assertSame(1, $component->unreadCount);
    }
}
