<?php

use App\Http\Livewire\Group\Settings\Index as GroupSettingsIndex;
use App\Models\Group\Category;
use App\Models\Group\Group;
use App\Models\User;

/**
 * Filament focused checks to ensure the component provides panel-ready data.
 */
it('returns categories that translate into Filament select options', function () {
    // Create a single category to verify that option builders will receive meaningful data.
    $category = Category::create([
        'name' => 'Trail Training',
        'slug' => 'trail-training',
    ]);

    $owner = User::factory()->create();

    $group = Group::create([
        'name' => 'Adventure Pups',
        'slug' => 'adventure-pups',
        'visibility' => Group::VISIBILITY_OPEN,
        'category_id' => $category->id,
        'creator_id' => $owner->id,
    ]);

    // Mount the component manually to simulate the lifecycle Filament panels trigger.
    $component = new GroupSettingsIndex();
    $component->mount($group);

    // Resolve the view data so we can prove it maps cleanly into a Filament select input.
    $viewData = $component->render()->getData();
    $options = $viewData['categories']->pluck('name', 'id')->toArray();

    expect($options)->toHaveKey($category->id)
        ->and($options[$category->id])->toBe('Trail Training');
});
