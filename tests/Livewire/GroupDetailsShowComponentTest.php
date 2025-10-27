<?php

use App\Http\Livewire\Group\Details\Show;
use App\Models\Group\Category;
use App\Models\Group\Group;
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

it('removes members from the roster when the action is triggered', function (): void {
    // Provision a group owner and a participant so the removal operation has a target pivot row.
    $creator = User::factory()->create();
    $participant = User::factory()->create();

    $group = Group::query()->create([
        'name' => 'Roster Maintenance Guild',
        'slug' => sprintf('roster-maintenance-guild-%s', Str::uuid()),
        'description' => 'Verifies that roster pruning functions as expected.',
        'category_id' => null,
        'visibility' => 'open',
        'creator_id' => $creator->id,
        'location' => 'Operations Deck',
        'rules' => ['Keep member lists tidy.'],
    ]);

    // Attach the target participant before we attempt to remove them from the pivot table.
    $group->members()->attach($creator->id, ['role' => 'admin', 'status' => 'active']);
    $group->members()->attach($participant->id, ['role' => 'member', 'status' => 'active']);

    $this->actingAs($creator);

    Livewire::test(Show::class, ['group' => $group])
        ->call('removeMember', $participant->id)
        ->assertDispatched('memberRemoved');

    // Ensure the pivot row was deleted so the removed member no longer appears in the roster.
    $participantStillAttached = $group->fresh()
        ->members()
        ->where('users.id', $participant->id)
        ->exists();

    expect($participantStillAttached)->toBeFalse();
});

it('validates invite details and clears the form upon success', function (): void {
    // Set up a moderator who will send invites so membership checks succeed.
    $moderator = User::factory()->create();

    $group = Group::query()->create([
        'name' => 'Invitation Flow Council',
        'slug' => sprintf('invitation-flow-council-%s', Str::uuid()),
        'description' => 'Tracks improvements to invitation ergonomics.',
        'category_id' => null,
        'visibility' => 'closed',
        'creator_id' => $moderator->id,
        'location' => 'Messaging Hub',
        'rules' => ['Confirm contact details before inviting.'],
    ]);

    $group->members()->attach($moderator->id, ['role' => 'admin', 'status' => 'active']);

    $this->actingAs($moderator);

    $component = Livewire::test(Show::class, ['group' => $group]);

    // First, trigger validation to confirm malformed emails are rejected.
    $component
        ->set('inviteEmail', 'not-a-valid-email')
        ->call('inviteMember')
        ->assertHasErrors(['inviteEmail' => 'email']);

    // Provide valid details to ensure the invite form resets and closes the modal.
    $component
        ->set('inviteEmail', 'new.member@example.com')
        ->set('inviteMessage', 'Join us for weekly collaboration sessions!')
        ->set('showInviteModal', true)
        ->call('inviteMember')
        ->assertHasNoErrors()
        ->assertSet('inviteEmail', '')
        ->assertSet('inviteMessage', '')
        ->assertSet('showInviteModal', false);
});
