<?php

use App\Http\Livewire\Group\Details\Show;
use App\Models\Group\Category;
use App\Models\Group\Group;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * Unit tests covering isolated behaviour within the Show component.
 */
it('loads persisted group metadata into the component state', function (): void {
    // Provision a creator and associated category so the group instance mirrors production data.
    $creator = User::factory()->create();
    $category = Category::query()->create([
        'name' => 'Community Clubs',
        'slug' => sprintf('community-%s', Str::uuid()),
    ]);

    // Save a group with descriptive metadata and starter rules.
    $group = Group::query()->create([
        'name' => 'Morning Trailblazers',
        'slug' => sprintf('morning-trailblazers-%s', Str::uuid()),
        'description' => 'Sunrise hikes organised every weekend.',
        'category_id' => $category->id,
        'visibility' => 'open',
        'creator_id' => $creator->id,
        'location' => 'Pine Ridge',
        'rules' => ['Arrive 10 minutes early.'],
    ]);

    // Instantiate the component directly so we can exercise the data-loading helper.
    $component = new Show();
    $component->group = $group->fresh();
    $component->loadGroupData();

    // Verify that every public property mirrors the underlying group record.
    expect($component->name)->toBe($group->name)
        ->and($component->description)->toBe($group->description)
        ->and($component->visibility)->toBe($group->visibility)
        ->and($component->location)->toBe($group->location)
        ->and($component->rules)->toBe($group->rules);
});

it('switches tabs using the dedicated state helper', function (): void {
    // Instantiate the component without touching the database to confirm the setter is self-contained.
    $component = new Show();

    // Change the tab a few times to ensure the active state is reassigned deterministically.
    $component->setActiveTab('members');
    expect($component->activeTab)->toBe('members');

    $component->setActiveTab('events');
    expect($component->activeTab)->toBe('events');
});
