<?php

use App\Http\Livewire\Group\Forms\Create;
use App\Models\Group\Category;
use Illuminate\Support\Facades\Cache;

/**
 * Unit level assertions for the Create group Livewire component.
 */
describe('Group create component unit characteristics', function () {
    it('defaults to the expected initial state', function () {
        // Instantiate the component directly to inspect its default property values.
        $component = new Create();

        expect($component->visibility)->toBe('open');
        expect($component->name)->toBeNull();
        expect($component->description)->toBeNull();
        expect($component->coverImage)->toBeNull();
        expect($component->icon)->toBeNull();
        expect($component->categoryId)->toBeNull();
        expect($component->location)->toBeNull();
    });

    it('exposes the validation rule blueprint required by the feature tests', function () {
        // Reflect the protected $rules property so we can make assertions without coupling to Livewire internals.
        $component = new Create();
        $reflection = new ReflectionClass($component);
        $property = $reflection->getProperty('rules');
        $property->setAccessible(true);

        $rules = $property->getValue($component);

        // Assert that each field includes the intended constraints for downstream integrations.
        expect($rules['name'])->toBe('required|min:3|max:255');
        expect($rules['description'])->toBe('required|min:10|max:1000');
        expect($rules['visibility'])->toBe('required|in:open,closed,secret');
        expect($rules['coverImage'])->toBe('nullable|image|max:2048');
        expect($rules['icon'])->toBe('nullable|image|max:1024');
        expect($rules['categoryId'])->toBe('required|exists:group_categories,id');
        expect($rules['location'])->toBe('nullable|string|max:255');
    });

    it('renders the expected blade while only exposing active categories', function () {
        // Clear cached lookups so the render method fetches fresh category collections for the assertion.
        Cache::flush();

        // Seed a mix of active and inactive categories to validate the filtering logic inside render().
        $activeCategory = Category::query()->create([
            'name' => 'Trail Crews',
            'slug' => 'trail-crews',
            'description' => 'Active hiking squads available to new members.',
            'display_order' => 1,
            'is_active' => true,
        ]);
        Category::query()->create([
            'name' => 'Archived Circles',
            'slug' => 'archived-circles',
            'description' => 'Hidden groups that should not appear in create forms.',
            'display_order' => 2,
            'is_active' => false,
        ]);

        // Call render() directly to obtain the Illuminate\View\View instance and inspect its payload.
        $component = new Create();
        $view = $component->render();

        // Ensure the blade reference remains stable for embedding in other layouts.
        expect($view->name())->toBe('livewire.group.forms.create');

        // Confirm the categories dataset only contains active entries.
        $categories = $view->getData()['categories'];
        expect($categories->pluck('id')->all())
            ->toContain($activeCategory->id)
            ->and($categories->pluck('slug')->all())
            ->not->toContain('archived-circles');
    });
});
