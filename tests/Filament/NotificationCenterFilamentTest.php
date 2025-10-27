<?php

use App\Http\Livewire\Common\NotificationCenter;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;

// Refresh the schema to mimic the environment Filament widgets would expect.
uses(RefreshDatabase::class);

it('exposes filter data compatible with Filament select filters', function (): void {
    // Reset cache to ensure the Livewire component repopulates its filter metadata.
    Cache::flush();

    // Create a user with notifications across multiple categories and priorities.
    $user = User::factory()->create();
    UserNotification::factory()->for($user)->create([
        'category' => 'system',
        'priority' => 'normal',
    ]);
    UserNotification::factory()->for($user)->create([
        'category' => 'engagement',
        'priority' => 'high',
    ]);

    $this->actingAs($user);

    // Assert the component exposes option lists that Filament tables can consume without transformation.
    Livewire::test(NotificationCenter::class, ['entityType' => 'user', 'entityId' => $user->id])
        ->assertSet('availableCategories', array_keys(config('notifications.categories')))
        ->assertSet('availablePriorities', config('notifications.priorities'));
});

it('filters notifications using category and priority combinations suitable for Filament tables', function (): void {
    // Clear cached counts so the Livewire component recomputes the filtered dataset.
    Cache::flush();

    // Seed notifications with distinct categories and priorities to validate filtering logic.
    $user = User::factory()->create();
    $system = UserNotification::factory()->for($user)->create([
        'category' => 'system',
        'priority' => 'high',
        'message' => 'Security alert',
    ]);
    UserNotification::factory()->for($user)->create([
        'category' => 'engagement',
        'priority' => 'normal',
        'message' => 'New comment received',
    ]);

    $this->actingAs($user);

    // Apply both filters and confirm only the matching notification remains in the rendered collection.
    Livewire::test(NotificationCenter::class, ['entityType' => 'user', 'entityId' => $user->id])
        ->set('category', 'system')
        ->set('priority', 'high')
        ->call('$refresh')
        ->assertViewHas('notifications', function ($paginator) use ($system): bool {
            // Filament tables consume LengthAwarePaginator instances, so we assert the collection contains the expected row.
            return $paginator->count() === 1 && $paginator->first()->is($system);
        });
});
