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
