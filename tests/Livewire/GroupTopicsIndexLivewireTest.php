<?php

use App\Http\Livewire\Group\Topics\Index;
use App\Models\Group\Group;
use App\Models\Group\Topic;
use App\Models\User;
use Livewire\Livewire;

it('filters regular topics when the search field is updated', function () {
    // Generate a member that will act as the authenticated viewer.
    $member = User::factory()->create();

    // Create the host group so the Livewire component can resolve its dependencies.
    $group = Group::create([
        'name' => 'Searchable Group',
        'slug' => 'searchable-group',
        'description' => 'Group used to validate Livewire searching.',
        'visibility' => 'open',
        'creator_id' => $member->id,
        'is_active' => true,
    ]);

    // Register the member within the pivot table to reflect real access patterns.
    $group->members()->attach($member->id, [
        'role' => 'member',
        'status' => 'active',
        'joined_at' => now(),
    ]);

    // Store two topics so only the matching one survives the search constraint.
    $matchingTopic = Topic::create([
        'group_id' => $group->id,
        'user_id' => $member->id,
        'title' => 'Pawsome Adventures',
        'content' => 'Highlights from the weekend walk.',
    ]);

    Topic::create([
        'group_id' => $group->id,
        'user_id' => $member->id,
        'title' => 'Grooming Checklist',
        'content' => 'Daily routine for long haired pups.',
    ]);

    // Authenticate as the group member to mirror production behavior.
    $this->actingAs($member);

    // Apply the search term and confirm only the matching topic remains in the paginator.
    Livewire::test(Index::class, ['group' => $group])
        ->set('search', 'Pawsome')
        ->call('render')
        ->assertViewHas('regularTopics', function ($topics) use ($matchingTopic) {
            // The filtered collection should contain the matching topic id exclusively.
            return $topics->pluck('id')->contains($matchingTopic->id)
                && $topics->count() === 1;
        });
});
