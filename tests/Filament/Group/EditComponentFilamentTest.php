<?php

use App\Http\Livewire\Group\Forms\Edit;
use App\Models\Group\Category;
use App\Models\Group\Group;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

it('supplies category options that Filament select fields can consume', function () {
    // Ensure the shared schema exists before seeding data for the option assertions.
    prepareTestDatabase();

    // Clear cached lookups so the component pulls a deterministic collection.
    Cache::flush();

    // Prepare one active and one inactive category to mirror Filament option filtering.
    $creator = User::factory()->create();
    $activeCategory = Category::query()->create([
        'name' => 'Community Builders',
        'slug' => 'community-builders',
        'description' => 'High energy collaboration crews',
        'display_order' => 3,
        'is_active' => true,
    ]);
    $inactiveCategory = Category::query()->create([
        'name' => 'Archived Hangouts',
        'slug' => 'archived-hangouts',
        'description' => 'Retired meetups hidden from menus',
        'display_order' => 99,
        'is_active' => false,
    ]);

    // Mount the component so it resolves the backing group and shares view data.
    $group = Group::query()->create([
        'name' => 'Community Council',
        'slug' => 'community-council',
        'description' => 'Leads collaborative events across the region.',
        'category_id' => $activeCategory->id,
        'visibility' => Group::VISIBILITY_OPEN,
        'creator_id' => $creator->id,
        'location' => 'Town Square',
    ]);
    $component = new Edit();
    $component->mount($group);

    // Simulate how a Filament Select component would pull options from the exposed dataset.
    $options = Category::getActiveCategories()->pluck('name', 'id')->toArray();

    expect($options)->toHaveKey($activeCategory->id, 'Community Builders');
    expect($options)->not->toHaveKey($inactiveCategory->id);
});
