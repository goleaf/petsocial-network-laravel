<?php

use App\Http\Livewire\Group\Forms\Edit;
use App\Models\Group\Category;
use App\Models\Group\Group;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;

it('enforces validation rules during live updates', function () {
    // Guarantee the schema is available even when Pest hooks are bypassed by the runtime.
    prepareTestDatabase();

    // Reset cached category collections so the component pulls the latest data.
    Cache::flush();

    // Create the prerequisite entities that the component expects during hydration.
    $creator = User::factory()->create();
    $category = Category::query()->create([
        'name' => 'Trail Teams',
        'slug' => 'trail-teams',
        'description' => 'Coordinated hiking crews',
        'display_order' => 5,
        'is_active' => true,
    ]);
    $group = Group::query()->create([
        'name' => 'Trail Blazers',
        'slug' => 'trail-blazers',
        'description' => 'Weekly exploration squads that log every route.',
        'category_id' => $category->id,
        'visibility' => Group::VISIBILITY_OPEN,
        'creator_id' => $creator->id,
        'location' => 'Forest Edge',
    ]);

    // Submit an invalid name to trigger the `min` rule while keeping other data intact.
    Livewire::test(Edit::class, ['group' => $group])
        ->set('name', 'No')
        ->call('updateGroup')
        ->assertHasErrors(['name' => 'min']);

    // Ensure the persisted record keeps its original metadata when validation fails.
    $group->refresh();
    expect($group->name)->toBe('Trail Blazers');
});
