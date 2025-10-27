<?php

use App\Http\Livewire\Group\Forms\Edit;
use App\Models\Group\Group;

it('hydrates public properties when mounting the edit component', function () {
    // Craft an unsaved group instance that mirrors the data the component expects.
    $group = Group::make([
        'name' => 'Evening Explorers',
        'description' => 'After work hikes with community pets.',
        'visibility' => Group::VISIBILITY_SECRET,
        'category_id' => 11,
        'location' => 'Hidden Valley',
    ]);
    $group->id = 42;

    // Instantiate the component directly to verify mount side effects without Livewire runtime noise.
    $component = new Edit();
    $component->mount($group);

    // Assert each public property mirrors the backing model data for downstream form bindings.
    expect($component->group)->toBe($group);
    expect($component->name)->toBe('Evening Explorers');
    expect($component->description)->toBe('After work hikes with community pets.');
    expect($component->visibility)->toBe(Group::VISIBILITY_SECRET);
    expect($component->categoryId)->toBe(11);
    expect($component->location)->toBe('Hidden Valley');
});
