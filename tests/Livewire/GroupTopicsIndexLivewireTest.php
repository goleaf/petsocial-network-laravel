<?php

use App\Http\Livewire\Group\Topics\Index;
use App\Models\Group\Category;
use App\Models\Group\Group;
use App\Models\Group\Topic;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Livewire;

it('filters regular topics when the search field is updated', function () {
    // Recreate the testing schema so Livewire interactions have the necessary tables.
    prepareTestDatabase();

    // Seed a category so the group factory can respect foreign key requirements.
    $category = Category::create([
        'name' => 'Searchable',
        'slug' => 'searchable',
        'description' => 'Category for search-related tests.',
        'display_order' => 1,
        'is_active' => true,
    ]);

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
        'category_id' => $category->id,
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
        ->assertViewHas('regularTopics', function ($topics) use ($matchingTopic) {
            // The filtered collection should contain the matching topic id exclusively.
            return $topics->pluck('id')->contains($matchingTopic->id)
                && $topics->count() === 1;
        });
});

it('limits the paginator to the viewers topics when the mine filter is active', function (): void {
    // Ensure the temporary SQLite database mirrors production structure before seeding data.
    prepareTestDatabase();

    // Persist a shared category so multiple groups can be created without violating constraints.
    $category = Category::create([
        'name' => 'Discussion',
        'slug' => 'discussion',
        'description' => 'Category used to validate filtering logic.',
        'display_order' => 1,
        'is_active' => true,
    ]);

    // Create both the viewing member and another participant to validate filtering behaviour.
    $viewer = User::factory()->create();
    $otherMember = User::factory()->create();

    // Persist the group and attach both participants to emulate a populated membership roster.
    $group = Group::create([
        'name' => 'Authored Threads',
        'description' => 'Testing container for mine filter validation.',
        'visibility' => 'open',
        'creator_id' => $viewer->id,
        'is_active' => true,
        'category_id' => $category->id,
    ]);

    $group->members()->attach($viewer->id, [
        'role' => 'member',
        'status' => 'active',
        'joined_at' => now(),
    ]);

    $group->members()->attach($otherMember->id, [
        'role' => 'member',
        'status' => 'active',
        'joined_at' => now(),
    ]);

    // Register one topic for each member so the filter has distinct authors to compare.
    $viewerTopic = Topic::create([
        'group_id' => $group->id,
        'user_id' => $viewer->id,
        'title' => 'Viewer Thoughts',
        'content' => 'A note that should survive the mine filter.',
    ]);

    $otherTopic = Topic::create([
        'group_id' => $group->id,
        'user_id' => $otherMember->id,
        'title' => 'Companion Update',
        'content' => 'A thread that should be hidden when filtering to mine.',
    ]);

    // Authenticate as the viewer before applying the mine filter in Livewire.
    $this->actingAs($viewer);

    Livewire::test(Index::class, ['group' => $group])
        ->set('filter', 'mine')
        ->assertViewHas('regularTopics', function (LengthAwarePaginator $topics) use ($viewerTopic, $otherTopic): bool {
            // The paginator should only contain the viewer-authored topic once the filter is active.
            $ids = $topics->pluck('id');

            return $ids->contains($viewerTopic->id)
                && ! $ids->contains($otherTopic->id);
        });
});

it('persists a child topic when the parentTopicId field is populated', function (): void {
    // Prepare the SQLite schema so the Livewire component can store topics safely.
    prepareTestDatabase();

    // Seed the required category referenced by the group model.
    $category = Category::create([
        'name' => 'Hierarchy',
        'slug' => 'hierarchy',
        'description' => 'Category to confirm nested topic creation.',
        'display_order' => 1,
        'is_active' => true,
    ]);

    // Create the member who will author both parent and child topics.
    $member = User::factory()->create();

    // Persist the owning group for the threaded discussion.
    $group = Group::create([
        'name' => 'Nested Threads',
        'description' => 'Group verifying child topic creation.',
        'visibility' => 'open',
        'creator_id' => $member->id,
        'is_active' => true,
        'category_id' => $category->id,
    ]);

    $group->members()->attach($member->id, [
        'role' => 'member',
        'status' => 'active',
        'joined_at' => now(),
    ]);

    // Seed a parent topic that should appear in the available parent options.
    $parentTopic = Topic::create([
        'group_id' => $group->id,
        'user_id' => $member->id,
        'title' => 'Parent Anchor',
        'content' => 'Root discussion that will host child topics.',
    ]);

    $this->actingAs($member);

    Livewire::test(Index::class, ['group' => $group])
        ->set('title', 'Follow-up Topic')
        ->set('content', 'Extended conversation continuing the parent discussion.')
        ->set('parentTopicId', $parentTopic->id)
        ->call('createTopic')
        ->assertSet('parentTopicId', null); // Ensure the form resets so future topics start as root entries.

    $this->assertDatabaseHas('group_topics', [
        'title' => 'Follow-up Topic',
        'parent_id' => $parentTopic->id,
    ]);
});
