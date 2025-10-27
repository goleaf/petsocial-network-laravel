<?php

use App\Http\Livewire\Group\Management\Index;
use App\Models\Group\Category;
use App\Models\Group\Group;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

it('filters groups by search text and membership visibility through Livewire state', function (): void {
    // Prepare the authenticated viewer required by the component filters.
    $member = User::factory()->create();
    actingAs($member);

    // Reset cached category lists so the freshly inserted categories are discoverable.
    Cache::flush();

    $category = Category::query()->create([
        'name' => 'Community',
        'slug' => 'community',
        'is_active' => true,
    ]);

    // Seed groups with distinct visibilities and membership relations to exercise filter logic.
    $openGroup = Group::query()->create([
        'name' => 'Trail Explorers',
        'slug' => Group::generateUniqueSlug('Trail Explorers'),
        'description' => 'Weekend scouts and mapping enthusiasts',
        'category_id' => $category->id,
        'visibility' => Group::VISIBILITY_OPEN,
        'creator_id' => $member->id,
        'location' => 'Denver',
    ]);

    $closedGroup = Group::query()->create([
        'name' => 'Members Lounge',
        'slug' => Group::generateUniqueSlug('Members Lounge'),
        'description' => 'Invite-only hangouts for long-term supporters',
        'category_id' => $category->id,
        'visibility' => Group::VISIBILITY_CLOSED,
        'creator_id' => $member->id,
        'location' => 'Seattle',
    ]);

    $secretGroup = Group::query()->create([
        'name' => 'Hidden Trailheads',
        'slug' => Group::generateUniqueSlug('Hidden Trailheads'),
        'description' => 'Coordinate scouting missions quietly',
        'category_id' => $category->id,
        'visibility' => Group::VISIBILITY_SECRET,
        'creator_id' => $member->id,
        'location' => 'Portland',
    ]);

    // Attach the viewer to the closed group so the "my" filter has a match.
    $closedGroup->members()->syncWithoutDetaching([
        $member->id => [
            'role' => 'member',
            'status' => 'active',
            'joined_at' => now(),
        ],
    ]);

    // Interact with the Livewire component to verify search-driven filtering semantics.
    Livewire::test(Index::class)
        ->set('search', 'Trail')
        ->assertViewHas('groups', function (LengthAwarePaginator $groups) use ($openGroup, $secretGroup, $closedGroup): bool {
            // Only groups referencing the search phrase should remain visible to the viewer.
            return $groups->contains('id', $openGroup->id)
                && $groups->contains('id', $secretGroup->id)
                && $groups->doesntContain('id', $closedGroup->id);
        })
        ->set('search', '')
        ->set('filter', 'my')
        ->assertViewHas('groups', function (LengthAwarePaginator $groups) use ($closedGroup): bool {
            // The membership-focused filter should isolate groups the viewer actively belongs to.
            return $groups->count() === 1 && $groups->first()->id === $closedGroup->id;
        })
        ->set('filter', 'open')
        ->assertViewHas('groups', function (LengthAwarePaginator $groups) use ($openGroup): bool {
            // Visibility filters should restrict the paginator to groups matching the selected type.
            return $groups->count() === 1 && $groups->first()->id === $openGroup->id;
        });
});

it('renders the management blade view with the expected layout container', function (): void {
    // Authenticate a viewer so Livewire can hydrate the component state for a signed-in member.
    $viewer = User::factory()->create();
    actingAs($viewer);

    // Reset cached categories and seed a single record to confirm the view touches blade iterations.
    Cache::flush();
    Category::query()->create([
        'name' => 'Logistics',
        'slug' => 'logistics',
        'is_active' => true,
    ]);

    // Issue a lightweight render and ensure the blade template and root test identifier are exposed.
    Livewire::test(Index::class)
        ->assertViewIs('livewire.group.management.index')
        ->assertSeeHtml('data-testid="group-management-index-root"');
});
