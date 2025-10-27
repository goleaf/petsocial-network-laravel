<?php

use App\Http\Livewire\Group\Details\Show;
use App\Models\Group\Category;
use App\Models\Group\Group;
use App\Models\Group\Resource as GroupResource;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Livewire;

use function Pest\Laravel\assertDatabaseHas;

/**
 * Livewire interaction tests for the group details component.
 */
it('updates group metadata and stores uploaded media', function (): void {
    // Fake the public disk so file uploads do not touch the real filesystem.
    Storage::fake('public');

    // Create a creator and category so the component receives realistic dependencies.
    $creator = User::factory()->create();
    $category = Category::query()->create([
        'name' => 'Trail Groups',
        'slug' => sprintf('trail-%s', Str::uuid()),
    ]);

    // Persist a group that will be mutated during the Livewire interaction.
    $group = Group::query()->create([
        'name' => 'Weekend Wanderers',
        'slug' => sprintf('weekend-wanderers-%s', Str::uuid()),
        'description' => 'Planning scenic weekend outings.',
        'category_id' => $category->id,
        'visibility' => 'open',
        'creator_id' => $creator->id,
        'location' => 'Lakeview',
        'rules' => ['Share photos after each trip.'],
    ]);

    // Authenticate as the group owner to satisfy the component expectations.
    $this->actingAs($creator);

    Livewire::test(Show::class, ['group' => $group])
        // Provide updated metadata to exercise the validation rules.
        ->set('showEditModal', true)
        ->set('name', 'Sunset Wanderers')
        ->set('description', 'Curating golden-hour adventures for the community.')
        ->set('category', 'community-events')
        ->set('visibility', 'closed')
        ->set('location', 'Seaside Promenade')
        ->set('rules', ['Bring snacks to share.'])
        ->set('newCoverImage', UploadedFile::fake()->image('cover.jpg'))
        ->set('newIcon', UploadedFile::fake()->image('icon.png'))
        ->call('updateGroup')
        ->assertSet('showEditModal', false);

    // Refresh the group to confirm mutations persisted to the database.
    $updatedGroup = $group->fresh();

    expect($updatedGroup->name)->toBe('Sunset Wanderers')
        ->and($updatedGroup->description)->toBe('Curating golden-hour adventures for the community.')
        ->and($updatedGroup->visibility)->toBe('closed')
        ->and($updatedGroup->location)->toBe('Seaside Promenade')
        ->and($updatedGroup->rules)->toBe(['Bring snacks to share.']);

    // Uploaded files should be stored on the fake disk using the configured directories.
    Storage::disk('public')->assertExists($updatedGroup->cover_image);
    Storage::disk('public')->assertExists($updatedGroup->icon);
});

it('reassigns member roles through the pivot relationship', function (): void {
    // Establish the group creator and a member whose role will be elevated.
    $creator = User::factory()->create();
    $member = User::factory()->create();

    $group = Group::query()->create([
        'name' => 'Moderation Taskforce',
        'slug' => sprintf('moderation-taskforce-%s', Str::uuid()),
        'description' => 'Group dedicated to moderation best practices.',
        'category_id' => null,
        'visibility' => 'closed',
        'creator_id' => $creator->id,
        'location' => 'Community HQ',
        'rules' => ['Review reports weekly.'],
    ]);

    // Attach both users with explicit roles so updates can be asserted precisely.
    $group->members()->attach($creator->id, ['role' => 'admin', 'status' => 'active']);
    $group->members()->attach($member->id, ['role' => 'member', 'status' => 'active']);

    $this->actingAs($creator);

    Livewire::test(Show::class, ['group' => $group])
        ->set('selectedMembers', [$member->id])
        ->set('memberRole', 'moderator')
        ->set('showMembersModal', true)
        ->call('updateMemberRoles')
        ->assertSet('selectedMembers', [])
        ->assertSet('showMembersModal', false);

    $updatedRole = $group->fresh()
        ->members()
        ->where('users.id', $member->id)
        ->first()
        ->pivot
        ->role;

    expect($updatedRole)->toBe('moderator');
});

it('records a report when a member flags the group', function (): void {
    // Prepare a member with access so the reporting workflow can run end-to-end.
    $member = User::factory()->create();

    $group = Group::query()->create([
        'name' => 'Feedback Circle',
        'slug' => sprintf('feedback-circle-%s', Str::uuid()),
        'description' => 'A hub for sharing candid platform feedback.',
        'category_id' => null,
        'visibility' => 'open',
        'creator_id' => $member->id,
        'location' => 'Virtual',
        'rules' => ['Stay constructive.'],
    ]);

    // Ensure the reporting member is attached so membership checks succeed.
    $group->members()->attach($member->id, ['role' => 'admin', 'status' => 'active']);

    $this->actingAs($member);

    Livewire::test(Show::class, ['group' => $group])
        ->set('reportReason', 'The description includes outdated information.')
        ->call('reportGroup')
        ->assertSet('reportReason', '');

    // Confirm the morph relationship persisted the report with the supplied reason.
    assertDatabaseHas('reports', [
        'reportable_type' => Group::class,
        'reportable_id' => $group->id,
        'user_id' => $member->id,
        'reason' => 'The description includes outdated information.',
    ]);
});

it('allows active members to share helpful link resources', function (): void {
    // Cache-free filesystem ensures the upload assertions focus on behaviour not IO.
    Storage::fake('public');

    $member = User::factory()->create();

    $group = Group::query()->create([
        'name' => 'Resource Collective',
        'slug' => sprintf('resource-collective-%s', Str::uuid()),
        'description' => 'Pooling references for upcoming events.',
        'category_id' => null,
        'visibility' => Group::VISIBILITY_OPEN,
        'creator_id' => $member->id,
        'location' => 'Digital Commons',
        'rules' => ['Credit original authors.'],
    ]);

    // Ensure the member is active within the group context for permission checks.
    $group->members()->attach($member->id, ['role' => 'member', 'status' => 'active']);

    $this->actingAs($member);

    Livewire::test(Show::class, ['group' => $group])
        ->set('activeTab', 'resources')
        ->set('resourceTitle', 'Trail maintenance guidelines')
        ->set('resourceDescription', 'Official park service resource for volunteer briefings.')
        ->set('resourceType', 'link')
        ->set('resourceUrl', 'https://example.com/guidelines')
        ->call('shareResource')
        ->assertSet('resourceTitle', '')
        ->assertSet('resourceDescription', '')
        ->assertSet('resourceUrl', '')
        ->assertSet('resourceType', 'link');

    $resource = GroupResource::query()->first();

    expect($resource)->not->toBeNull()
        ->and($resource->group_id)->toBe($group->id)
        ->and($resource->type)->toBe('link')
        ->and($resource->url)->toBe('https://example.com/guidelines')
        ->and($resource->description)->toBe('Official park service resource for volunteer briefings.');
});

it('stores uploaded documents with metadata for group resources', function (): void {
    Storage::fake('public');

    $member = User::factory()->create();

    $group = Group::query()->create([
        'name' => 'Event Planning Squad',
        'slug' => sprintf('event-planning-squad-%s', Str::uuid()),
        'description' => 'Coordinating logistics and responsibilities.',
        'category_id' => null,
        'visibility' => Group::VISIBILITY_OPEN,
        'creator_id' => $member->id,
        'location' => 'Operations Hub',
        'rules' => ['Share agendas before meetings.'],
    ]);

    $group->members()->attach($member->id, ['role' => 'member', 'status' => 'active']);

    $this->actingAs($member);

    $document = UploadedFile::fake()->create('agenda.pdf', 512, 'application/pdf');

    Livewire::test(Show::class, ['group' => $group])
        ->set('activeTab', 'resources')
        ->set('resourceTitle', 'Monthly agenda template')
        ->set('resourceType', 'document')
        ->set('resourceDocument', $document)
        ->call('shareResource')
        ->assertSet('resourceTitle', '')
        ->assertSet('resourceDocument', null)
        ->assertSet('resourceType', 'link');

    $resource = GroupResource::query()->firstOrFail();

    expect($resource->type)->toBe('document')
        ->and($resource->file_path)->not->toBeNull()
        ->and($resource->file_name)->toBe('agenda.pdf')
        ->and($resource->file_mime)->toBe('application/pdf')
        ->and($resource->file_size)->toBe($document->getSize());

    Storage::disk('public')->assertExists($resource->file_path);
});
