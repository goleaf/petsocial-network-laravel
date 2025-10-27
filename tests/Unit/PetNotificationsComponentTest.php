<?php

namespace Tests\Unit;

use App\Http\Livewire\Pet\Notifications;
use App\Models\Pet;
use App\Models\PetNotification;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View as IlluminateView;
use Livewire\Component as LivewireComponent;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Lightweight stub used to satisfy nested Livewire component references during view assertions.
 */
class PetNotificationsPlaceholderComponent extends LivewireComponent
{
    /**
     * Accept the dynamic parameter list expected by the production component.
     */
    public function mount(...$parameters): void
    {
        // No setup required for placeholder behaviour.
    }

    /**
     * Render the shared placeholder view leveraged by component tests.
     */
    public function render(): IlluminateView
    {
        return view('tests.livewire-placeholder');
    }
}

/**
 * Unit level assertions target the component's caching mechanics directly.
 */
class PetNotificationsComponentTest extends TestCase
{
    public function test_update_unread_count_caches_the_current_total(): void
    {
        // Rebuild the sqlite memory schema so the component interacts with the expected tables.
        prepareTestDatabase();
        preparePetNotificationSchema();

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

    public function test_render_exposes_notifications_view_with_paginated_data(): void
    {
        // Reset the transient database to a known state before rendering the Livewire component.
        prepareTestDatabase();
        preparePetNotificationSchema();

        // Create a pet owner with two notifications to ensure pagination returns data.
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
            'data' => ['action' => 'activity'],
        ]);

        $this->actingAs($owner);

        // Reset cache state and prepare the component instance for rendering.
        Cache::flush();
        $component = app(Notifications::class);
        $component->mount($pet->id);

        // Register placeholder Livewire components so nested mounts do not pull in unrelated dependencies.
        Livewire::component('common.notification-center', PetNotificationsPlaceholderComponent::class);
        Livewire::component('common.friend.button', PetNotificationsPlaceholderComponent::class);

        // Extend the view finder so the placeholder Blade snippets resolve during rendering.
        $viewFinder = app('view')->getFinder();
        $originalPaths = $viewFinder->getPaths();
        $viewFinder->setPaths(array_merge([resource_path('views/tests')], $originalPaths));

        try {
            $view = $component->render();

            // Ensure the component returns the expected Blade template with paginated data.
            $this->assertSame('livewire.pet.notifications', $view->name());
            $viewData = $view->getData();
            $this->assertArrayHasKey('notifications', $viewData);
            $this->assertInstanceOf(LengthAwarePaginator::class, $viewData['notifications']);
            $this->assertSame(2, $viewData['notifications']->total());
        } finally {
            // Restore the original view paths so subsequent tests use the standard lookup order.
            $viewFinder->setPaths($originalPaths);
        }
    }
}
