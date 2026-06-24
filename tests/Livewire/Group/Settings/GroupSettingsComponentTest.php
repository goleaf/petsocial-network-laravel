<?php

use App\Http\Livewire\Group\Settings\Index as GroupSettingsIndex;
use App\Models\Group\Category;
use App\Models\Group\Group;
use App\Models\User;
use Livewire\Livewire;

/**
 * Livewire specific checks for the group settings component lifecycle.
 */
it('rejects unsupported visibility selections', function () {
    // Establish a valid group so the component can be mounted like the browser would.
    $category = Category::create([
        'name' => 'Training Tips',
        'slug' => 'training-tips',
    ]);

    $owner = User::factory()->create();

    $group = Group::create([
        'name' => 'Obedience Club',
        'slug' => 'obedience-club',
        'visibility' => Group::VISIBILITY_OPEN,
        'category_id' => $category->id,
        'creator_id' => $owner->id,
    ]);

    Livewire::test(GroupSettingsIndex::class, ['group' => $group])
        ->set('visibility', 'hidden')
        ->call('updateSettings')
        ->assertHasErrors(['visibility' => 'in']);
});

it('shares category data with the rendered view', function () {
    // Create a category so the template has a visible option to list.
    $category = Category::create([
        'name' => 'Health & Wellness',
        'slug' => 'health-wellness',
    ]);

    $owner = User::factory()->create();

    $group = Group::create([
        'name' => 'Wellness Pack',
        'slug' => 'wellness-pack',
        'visibility' => Group::VISIBILITY_CLOSED,
        'category_id' => $category->id,
        'creator_id' => $owner->id,
    ]);

    Livewire::test(GroupSettingsIndex::class, ['group' => $group])
        // Confirm the component resolves the correct Blade template so Livewire wiring stays intact.
        ->assertViewIs('livewire.group.settings.index')
        ->assertViewHas('categories', function ($categories) use ($category) {
            // Confirm the collection is suitable for select inputs in the view layer.
            return $categories->pluck('name', 'id')->get($category->id) === $category->name;
        });
});
