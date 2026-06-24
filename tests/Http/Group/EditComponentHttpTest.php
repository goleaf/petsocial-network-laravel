<?php

use App\Http\Livewire\Group\Forms\Edit;
use App\Models\Group\Category;
use App\Models\Group\Group;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

/**
 * HTTP coverage ensuring the group edit Livewire component can be served via standard routes.
 */
it('renders the group edit form when proxied through an http route', function () {
    // Stand up the schema so model factories and Eloquent create calls have backing tables.
    prepareTestDatabase();

    // Reset cached lookups to guarantee the component pulls a fresh category collection.
    Cache::flush();

    // Create the authenticated owner alongside the active category required for the select menu.
    $owner = User::factory()->create();
    $category = Category::query()->create([
        'name' => 'Dawn Patrol',
        'slug' => 'dawn-patrol',
        'description' => 'Sunrise crews exploring nearby trails.',
        'display_order' => 1,
        'is_active' => true,
    ]);

    // Persist a group instance that mirrors what maintainers would edit via the management dashboard.
    $group = Group::query()->create([
        'name' => 'Trail Guardians',
        'slug' => 'trail-guardians',
        'description' => 'Coordinated cleanup hikes keeping habitats pristine.',
        'visibility' => Group::VISIBILITY_OPEN,
        'category_id' => $category->id,
        'creator_id' => $owner->id,
        'location' => 'Valley Overlook',
    ]);

    // Register a lightweight HTTP route that proxies requests directly to the Livewire component.
    Route::middleware('web')->get('/testing/groups/{group}/edit', Edit::class)->name('testing.groups.edit');

    // Authenticate the owner so the request session and authorization layers mirror production usage.
    $this->actingAs($owner);

    // Dispatch the GET request and ensure the rendered blade includes the Livewire bindings.
    $this->withoutVite();
    $response = $this->get(sprintf('/testing/groups/%s/edit', $group->getRouteKey()));

    $response->assertOk();
    $response->assertSee('Save group');
    $response->assertSee('wire:submit.prevent="updateGroup"', false);
});
