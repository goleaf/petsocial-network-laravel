<?php

use App\Http\Livewire\Group\Settings\Index as GroupSettingsIndex;
use App\Models\Group\Category;
use App\Models\Group\Group;
use App\Models\User;
use Illuminate\View\View;

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

it('renders the expected Blade view with category context', function () {
    // Create the related data so the render cycle can pull categories from the database.
    $category = Category::create([
        'name' => 'Service Animals',
        'slug' => 'service-animals',
    ]);

    $owner = User::factory()->create();

    $group = Group::create([
        'name' => 'Assistance Squad',
        'slug' => 'assistance-squad',
        'visibility' => Group::VISIBILITY_OPEN,
        'category_id' => $category->id,
        'creator_id' => $owner->id,
    ]);

    $component = new GroupSettingsIndex();
    $component->mount($group);

    $view = $component->render();

    expect($view)->toBeInstanceOf(View::class)
        // Confirm the Blade template Livewire references is available and correctly named.
        ->and($view->getName())->toBe('livewire.group.settings.index')
        // Ensure the rendered data keeps the category collection accessible to the UI.
        ->and($view->getData()['categories']->contains('id', $category->id))->toBeTrue();
});
