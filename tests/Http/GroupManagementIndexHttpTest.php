<?php

use App\Models\Group\Category;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

it('renders the group management dashboard over HTTP for authenticated members', function (): void {
    // Authenticate a viewer to satisfy the middleware protecting the group routes.
    $viewer = User::factory()->create();
    actingAs($viewer);

    // Seed a visible category so the component view has supporting data to reference.
    Cache::flush();
    Category::query()->create([
        'name' => 'Weekend Warriors',
        'slug' => 'weekend-warriors',
        'is_active' => true,
    ]);

    // Issue an HTTP request to the Livewire endpoint and confirm the component boots successfully.
    $response = get(route('group.index'));

    $response->assertOk();
    $response->assertSee('group-management-index-root');
    $response->assertSeeLivewire('group.forms.create');
});
