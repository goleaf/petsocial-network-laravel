<?php

use App\Http\Livewire\Group\Topics\Index;
use App\Models\Group\Category;
use App\Models\Group\Group;
use App\Models\Group\Topic;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Livewire;
use function Pest\Laravel\actingAs;

/**
 * Feature coverage for the group topics index component to validate high-level behaviour.
 */
it('separates pinned topics from regular topics within the rendered payload', function (): void {
    // Rebuild the in-memory SQLite schema so model factories can persist records safely.
    prepareTestDatabase();

    // Provide a baseline category so the group creation satisfies required foreign keys.
    $category = Category::create([
        'name' => 'Announcements',
        'slug' => 'announcements',
        'description' => 'General update space for group news.',
        'display_order' => 1,
        'is_active' => true,
    ]);

    // Create the member who owns and views the group topics surface.
    $member = User::factory()->create();

    // Seed a simple group so the component can resolve relationship queries during rendering.
    $group = Group::create([
        'name' => 'Pinned Showcase',
        'description' => 'Group focused on validating pinned topic placement.',
        'visibility' => 'open',
        'creator_id' => $member->id,
        'is_active' => true,
        'category_id' => $category->id,
    ]);

    // Record the authenticated member as an active participant of the group for realism.
    $group->members()->attach($member->id, [
        'role' => 'member',
        'status' => 'active',
        'joined_at' => now(),
    ]);

    // Persist one pinned topic and one regular topic that should appear in separate collections.
    $pinnedTopic = Topic::create([
        'group_id' => $group->id,
        'user_id' => $member->id,
        'title' => 'Pinned Guidelines',
        'content' => 'Important onboarding information.',
        'is_pinned' => true,
    ]);

    $regularTopic = Topic::create([
        'group_id' => $group->id,
        'user_id' => $member->id,
        'title' => 'Weekly Discussion',
        'content' => 'General catch-up thread for the week.',
    ]);

    // Authenticate as the group member so any auth() helpers resolve consistently.
    actingAs($member);

    // Render the component and confirm pinned topics are isolated from the paginated collection.
    Livewire::test(Index::class, ['group' => $group])
        ->assertViewIs('livewire.group.topics.index')
        ->assertViewHas('pinnedTopics', function ($topics) use ($pinnedTopic): bool {
            // Ensure the dedicated pinned collection contains the expected record only once.
            return $topics->pluck('id')->contains($pinnedTopic->id);
        })
        ->assertViewHas('regularTopics', function (LengthAwarePaginator $topics) use ($regularTopic, $pinnedTopic): bool {
            // Confirm the paginator includes the regular topic while excluding the pinned entry entirely.
            $ids = $topics->pluck('id');

            return $ids->contains($regularTopic->id)
                && ! $ids->contains($pinnedTopic->id);
        });
});

it('eager loads child topics so nested threading data is returned to the view', function (): void {
    // Reset the sqlite memory database to ensure clean table definitions for the scenario.
    prepareTestDatabase();

    // Provision a category so the group satisfies the foreign key requirements enforced by the schema.
    $category = Category::create([
        'name' => 'Threading',
        'slug' => 'threading',
        'description' => 'Category validating nested topic hierarchies.',
        'display_order' => 1,
        'is_active' => true,
    ]);

    // Create the member that authors both the parent and child topics for deterministic ownership.
    $member = User::factory()->create();

    // Instantiate the group that hosts the threaded discussion tree.
    $group = Group::create([
        'name' => 'Threaded Nest',
        'description' => 'Group to confirm nested child topics render beneath their parents.',
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

    // Create a parent topic that should surface within the regular paginator as the thread anchor.
    $parentTopic = Topic::create([
        'group_id' => $group->id,
        'user_id' => $member->id,
        'title' => 'Parent Thread',
        'content' => 'Discussion root that will host child topics.',
    ]);

    // Persist a child topic linked to the parent so the component can surface hierarchical data.
    $childTopic = Topic::create([
        'group_id' => $group->id,
        'user_id' => $member->id,
        'title' => 'Branch Conversation',
        'content' => 'Follow-up discussion under the parent.',
        'parent_id' => $parentTopic->id,
    ]);

    actingAs($member);

    Livewire::test(Index::class, ['group' => $group])
        ->assertViewHas('regularTopics', function (LengthAwarePaginator $topics) use ($parentTopic, $childTopic): bool {
            // Fetch the parent from the paginator and ensure the child id appears in its eager-loaded collection.
            $collection = $topics->getCollection();
            $parent = $collection->firstWhere('id', $parentTopic->id);

            return $parent !== null
                && $parent->childrenRecursive->pluck('id')->contains($childTopic->id)
                && ! $collection->pluck('id')->contains($childTopic->id);
        })
        ->assertViewHas('availableParentTopics', function ($topics) use ($parentTopic, $childTopic): bool {
            // Parent topics should be offered as selectable options, while children remain excluded.
            $ids = $topics->pluck('id');

            return $ids->contains($parentTopic->id)
                && ! $ids->contains($childTopic->id);
        });
});
