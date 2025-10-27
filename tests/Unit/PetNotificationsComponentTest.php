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

    /**
     * Guarantee the SQLite database file exists for RefreshDatabase migrations.
     */
    private function ensureSqliteDatabaseFileExists(): void
    {
        // The testing database lives in database/testing.sqlite for in-memory style runs.
        if (! file_exists(database_path('testing.sqlite'))) {
            // Touch the file so that the SQLite connection has a real target to migrate against.
            touch(database_path('testing.sqlite'));
        }
    }

    public function test_update_unread_count_caches_the_current_total(): void
    {
        // Prime the database with an owner, their pet, and a single unread notification.
        $this->ensureSqliteDatabaseFileExists();

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

    public function test_mark_as_read_refreshes_the_cached_total(): void
    {
        // Set up two notifications to ensure the markAsRead action decrements counts properly.
        $this->ensureSqliteDatabaseFileExists();

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
            'data' => ['action' => 'activity', 'activity_id' => 99],
        ]);

        $this->actingAs($owner);

        // Mount the component so the cache and unread counts align with the existing records.
        Cache::flush();
        $component = app(Notifications::class);
        $component->mount($pet->id);

        $cacheKey = "pet_{$pet->id}_unread_notifications_count";
        $this->assertSame(2, $component->unreadCount);

        // Invoke the markAsRead method which should clear and then rebuild the cache.
        $component->markAsRead($firstNotification->id);

        // Confirm the cache and component instance now reflect a single unread notification.
        $this->assertTrue(Cache::has($cacheKey));
        $this->assertSame(1, Cache::get($cacheKey));
        $this->assertSame(1, $component->unreadCount);

        // The database should persist the read timestamp for the targeted notification as well.
        $this->assertNotNull($firstNotification->fresh()->read_at);
    }
}
