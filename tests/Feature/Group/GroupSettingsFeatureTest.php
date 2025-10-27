<?php

use App\Http\Livewire\Group\Settings\Index as GroupSettingsIndex;
use App\Models\Group\Category;
use App\Models\Group\Group;
use App\Models\User;
use Livewire\Livewire;

/**
 * Feature coverage for the group settings Livewire workflow.
 */
it('updates the group visibility and category', function () {
    // Seed two categories so we can confirm the component persists the replacement option.
    $originalCategory = Category::create([
        'name' => 'Outdoor Adventures',
        'slug' => 'outdoor-adventures',
    ]);

    $replacementCategory = Category::create([
        'name' => 'Community Support',
        'slug' => 'community-support',
    ]);

    // Create a group owner so the Livewire component has an authenticated actor to operate with.
    $creator = User::factory()->create();

    $group = Group::create([
        'name' => 'Trail Tails',
        'slug' => 'trail-tails',
        'visibility' => Group::VISIBILITY_OPEN,
        'category_id' => $originalCategory->id,
        'creator_id' => $creator->id,
    ]);

    // Authenticate the owner to mirror the real settings screen permissions.
    $this->actingAs($creator);

    Livewire::test(GroupSettingsIndex::class, ['group' => $group])
        ->set('visibility', Group::VISIBILITY_CLOSED)
        ->set('categoryId', $replacementCategory->id)
        ->call('updateSettings')
        ->assertHasNoErrors();

    // Refresh the model to confirm the new configuration persisted to the database.
    $group->refresh();

    expect($group->visibility)->toBe(Group::VISIBILITY_CLOSED)
        ->and($group->category_id)->toBe($replacementCategory->id);
});
