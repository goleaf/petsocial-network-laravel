<?php

use App\Http\Livewire\Group\Management\Index;
use App\Models\Group\Category;
use App\Models\Group\Group;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

it('creates a group and promotes the creator to admin through the management component', function (): void {
    // Fake the public disk so uploaded images are routed to an in-memory filesystem during the scenario.
    Storage::fake('public');

    // Build the authenticated member and supporting category required by the management workflow.
    $member = User::factory()->create();
    $category = Category::query()->create([
        'name' => 'Trail Clubs',
        'slug' => 'trail-clubs',
        'is_active' => true,
    ]);

    actingAs($member);

    // Flush cached category lookups so the component reads the fresh dataset seeded above.
    Cache::flush();

    $cover = UploadedFile::fake()->image('cover.jpg', 1200, 400);
    $icon = UploadedFile::fake()->image('icon.png', 256, 256);

    // Drive the Livewire component to create a new group via the exposed form action.
    $component = Livewire::test(Index::class)
        ->set('showCreateModal', true)
        ->set('name', 'Sunrise Hikers')
        ->set('description', 'Plan dawn adventures with fellow early risers.')
        ->set('categoryId', $category->id)
        ->set('visibility', Group::VISIBILITY_OPEN)
        ->set('location', 'Denver')
        ->set('groupRules', ['Be respectful', 'Share trail conditions'])
        ->set('coverImage', $cover)
        ->set('icon', $icon)
        ->call('createGroup')
        ->assertHasNoErrors()
        ->assertSet('showCreateModal', false);

    // Capture the newly created group so additional assertions can inspect its persisted state.
    $createdGroup = Group::query()->where('name', 'Sunrise Hikers')->first();

    $component->assertRedirect(route('group.detail', $createdGroup));

    // Confirm the persisted payload includes slugs, file paths, and stored governance rules.
    expect($createdGroup)->not->toBeNull()
        ->and($createdGroup->slug)->toBe('sunrise-hikers')
        ->and($createdGroup->rules)->toBe(['Be respectful', 'Share trail conditions']);

    // Uploaded assets should exist on the faked disk once the group has been created.
    expect(Storage::disk('public')->exists($createdGroup->cover_image))->toBeTrue()
        ->and(Storage::disk('public')->exists($createdGroup->icon))->toBeTrue();

    // The creator should be attached as an active admin member according to the component contract.
    $pivot = $createdGroup->members()->where('users.id', $member->id)->first()?->pivot;

    expect($pivot)->not->toBeNull()
        ->and($pivot->role)->toBe('admin')
        ->and($pivot->status)->toBe('active')
        ->and($pivot->joined_at)->not->toBeNull();
});

it('allows members to join open groups instantly and queue closed groups for approval', function (): void {
    // Authenticate a member so Livewire interactions can resolve the current user context.
    $member = User::factory()->create();
    actingAs($member);

    // Ensure cached category collections are reset before seeding fresh taxonomy fixtures.
    Cache::flush();
    $category = Category::query()->create([
        'name' => 'City Adventures',
        'slug' => 'city-adventures',
        'is_active' => true,
    ]);

    // Create both open and closed groups to exercise the two join flows the component exposes.
    $openGroup = Group::query()->create([
        'name' => 'Morning Explorers',
        'slug' => Group::generateUniqueSlug('Morning Explorers'),
        'description' => 'Organises sunrise walks through rotating neighbourhoods.',
        'category_id' => $category->id,
        'visibility' => Group::VISIBILITY_OPEN,
        'creator_id' => $member->id,
    ]);

    $closedGroup = Group::query()->create([
        'name' => 'Invite Only Club',
        'slug' => Group::generateUniqueSlug('Invite Only Club'),
        'description' => 'Hosts curated meetups with limited capacity.',
        'category_id' => $category->id,
        'visibility' => Group::VISIBILITY_CLOSED,
        'creator_id' => $member->id,
    ]);

    // Join the open group and confirm the membership immediately activates with an informative flash message.
    Livewire::test(Index::class)
        ->call('joinGroup', $openGroup->id);

    $openGroup = $openGroup->fresh();
    $openMembership = $openGroup->members()->where('users.id', $member->id)->first()?->pivot;

    expect($openMembership)->not->toBeNull()
        ->and($openMembership->status)->toBe('active')
        ->and($openMembership->role)->toBe('member');
    expect(session('message'))->toBe('You have joined the group successfully!');

    // Clear the previous flash message so the pending flow can assert its own response cleanly.
    session()->forget('message');

    // Join the closed group and verify the membership is staged for moderator approval.
    Livewire::test(Index::class)
        ->call('joinGroup', $closedGroup->id);

    $closedGroup = $closedGroup->fresh();
    $closedMembership = $closedGroup->members()->where('users.id', $member->id)->first()?->pivot;

    expect($closedMembership)->not->toBeNull()
        ->and($closedMembership->status)->toBe('pending')
        ->and($closedMembership->role)->toBe('member')
        ->and($closedMembership->joined_at)->toBeNull();
    expect(session('message'))->toBe('Your request to join has been sent to the group administrators.');
});

it('allows members to leave a group and clears the associated membership pivot', function (): void {
    // Authenticate a group member who will request to leave the community.
    $member = User::factory()->create();
    actingAs($member);

    // Reset cached group datasets so the component renders fresh relationship data.
    Cache::flush();
    $category = Category::query()->create([
        'name' => 'Weekend Projects',
        'slug' => 'weekend-projects',
        'is_active' => true,
    ]);

    // Seed a group and attach the authenticated member to mimic an active membership row.
    $group = Group::query()->create([
        'name' => 'Build A Dog Park',
        'slug' => Group::generateUniqueSlug('Build A Dog Park'),
        'description' => 'Collaboratively improves local play spaces.',
        'category_id' => $category->id,
        'visibility' => Group::VISIBILITY_OPEN,
        'creator_id' => $member->id,
    ]);

    $group->members()->syncWithoutDetaching([
        $member->id => [
            'role' => 'member',
            'status' => 'active',
            'joined_at' => now(),
        ],
    ]);

    // Invoke the leave action and ensure the pivot row is removed alongside the flash message.
    Livewire::test(Index::class)
        ->call('leaveGroup', $group->id);

    $group = $group->fresh();
    $remainingMembership = $group->members()->where('users.id', $member->id)->exists();

    expect($remainingMembership)->toBeFalse();
    expect(session('message'))->toBe('You have left the group.');
});
