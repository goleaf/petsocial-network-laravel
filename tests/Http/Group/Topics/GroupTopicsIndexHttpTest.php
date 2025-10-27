<?php

use App\Http\Livewire\Group\Topics\Index;
use App\Models\Group\Category;
use App\Models\Group\Group;
use App\Models\Group\Topic;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;

/**
 * HTTP coverage ensures the component can be mounted through a traditional request lifecycle.
 */
it('returns topic counts when the component is proxied through an http endpoint', function (): void {
    // Refresh the SQLite schema before seeding the dataset for the HTTP interaction.
    prepareTestDatabase();

    // Create a supporting category so the group record satisfies its foreign key constraint.
    $category = Category::create([
        'name' => 'Announcements',
        'slug' => 'announcements',
        'description' => 'General update space for group news.',
        'display_order' => 1,
        'is_active' => true,
    ]);

    // Prepare the authenticated member who will access the bespoke testing route.
    $member = User::factory()->create();

    // Create a group with two contrasting topics to validate the JSON payload produced by the proxy route.
    $group = Group::create([
        'name' => 'HTTP Bridge Group',
        'description' => 'Group used to confirm the HTTP wrapper behaves.',
        'visibility' => 'open',
        'creator_id' => $member->id,
        'is_active' => true,
        'category_id' => $category->id,
    ]);

    // Associate the member with the group to reflect production membership relationships.
    $group->members()->attach($member->id, [
        'role' => 'member',
        'status' => 'active',
        'joined_at' => now(),
    ]);

    Topic::create([
        'group_id' => $group->id,
        'user_id' => $member->id,
        'title' => 'Pinned Summary',
        'content' => 'High level overview kept at the top.',
        'is_pinned' => true,
    ]);

    Topic::create([
        'group_id' => $group->id,
        'user_id' => $member->id,
        'title' => 'Daily Check-In',
        'content' => 'Routine discussion thread for members.',
    ]);

    // Define a throwaway route that mounts the Livewire component and proxies the rendered data as JSON.
    Route::get('/testing/group-topics-endpoint', function () use ($group) {
        // Instantiate the component manually so we can inspect the rendered view data before HTTP serialization.
        $component = app(Index::class);
        $component->mount($group);

        $view = $component->render();
        $data = $view->getData();

        return response()->json([
            'view' => $view->name(),
            'pinned' => $data['pinnedTopics']->count(),
            'regular' => $data['regularTopics']->count(),
            'parents' => $data['availableParentTopics']->count(),
        ]);
    });

    // Authenticate and execute the HTTP request, validating the JSON reflects the component state.
    actingAs($member);

    getJson('/testing/group-topics-endpoint')
        ->assertOk()
        ->assertJson([
            'view' => 'livewire.group.topics.index',
            'pinned' => 1,
            'regular' => 1,
            'parents' => 2,
        ]);
});
