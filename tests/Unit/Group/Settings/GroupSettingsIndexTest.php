<?php

use App\Http\Livewire\Group\Settings\Index as GroupSettingsIndex;
use App\Models\Group\Category;
use App\Models\Group\Group;
use App\Models\User;

/**
 * Unit level guarantees for the group settings Livewire component.
 */
it('initializes public properties from the provided group', function () {
    // Provision the minimum related data so the component can mirror the database state.
    $category = Category::create([
        'name' => 'Neighborhood Watch',
        'slug' => 'neighborhood-watch',
    ]);

    $owner = User::factory()->create();

    $group = Group::create([
        'name' => 'Block Buddies',
        'slug' => 'block-buddies',
        'visibility' => Group::VISIBILITY_SECRET,
        'category_id' => $category->id,
        'creator_id' => $owner->id,
    ]);

    // Mount the component to replicate the lifecycle used in production routes.
    $component = new GroupSettingsIndex();
    $component->mount($group);

    expect($component->group->is($group))->toBeTrue()
        ->and($component->visibility)->toBe(Group::VISIBILITY_SECRET)
        ->and($component->categoryId)->toBe($category->id);
});

it('exposes the validation rules expected by the front end forms', function () {
    // Instantiate a fresh component so we can introspect its rule definition.
    $component = new GroupSettingsIndex();

    // Use a bound closure to safely read the protected $rules property for verification.
    $rules = (fn () => $this->rules)->call($component);

    expect($rules)->toMatchArray([
        'visibility' => 'required|in:open,closed,secret',
        'categoryId' => 'required|exists:group_categories,id',
    ]);
});
