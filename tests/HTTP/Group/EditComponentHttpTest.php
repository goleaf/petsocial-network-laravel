<?php

use App\Models\Group\Category;
use App\Models\Group\Group;
use App\Models\User;
use Illuminate\Foundation\Vite;
use Illuminate\Support\Facades\Cache;

use function Pest\Laravel\actingAs;

it('renders the group detail route that the edit component redirects toward', function () {
    // Establish the schema before authenticating to avoid missing table exceptions.
    prepareTestDatabase();

    // Point Laravel's Vite helper at a lightweight manifest so blade layouts resolve assets.
    app(Vite::class)->useBuildDirectory('../tests/fixtures/vite');

    // Flush cached datasets to avoid stale category collections during routing.
    Cache::flush();

    // Authenticate as a creator so the route middleware passes and the component can resolve the group.
    $creator = User::factory()->create();
    actingAs($creator);

    // Persist a category and group that mirror data touched by the edit workflow.
    $category = Category::query()->create([
        'name' => 'Outreach Teams',
        'slug' => 'outreach-teams',
        'description' => 'Community service crews',
        'display_order' => 7,
        'is_active' => true,
    ]);
    $group = Group::query()->create([
        'name' => 'Outreach Organisers',
        'slug' => 'outreach-organisers',
        'description' => 'Coordinates volunteer drives each weekend.',
        'category_id' => $category->id,
        'visibility' => Group::VISIBILITY_OPEN,
        'creator_id' => $creator->id,
        'location' => 'Riverfront Pavilion',
    ]);

    // Visit the page and ensure the HTTP layer responds successfully with the Livewire payload.
    $response = $this->get(route('group.detail', $group));
    $response->assertOk();
});
