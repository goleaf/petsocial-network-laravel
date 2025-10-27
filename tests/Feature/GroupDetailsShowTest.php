<?php

use App\Http\Livewire\Group\Details\Show;
use App\Models\Group\Group;
use App\Models\User;
use Illuminate\Support\Str;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

/**
 * Feature coverage for the group details Livewire entry point.
 */
it('allows members to view a private group via the detail route', function (): void {
    // Create a user who will own the group to satisfy the creator relationship.
    $creator = User::factory()->create();

    // Create a private group owned by the creator so we can evaluate membership gates.
    $group = Group::query()->create([
        'name' => 'Evening Walkers',
        'slug' => sprintf('evening-walkers-%s', Str::uuid()),
        'description' => 'A private walking club for after work strolls.',
        'category_id' => null,
        'visibility' => 'private',
        'creator_id' => $creator->id,
        'location' => 'Downtown Riverside',
        'rules' => ['Be respectful during discussions.'],
    ]);

    // Attach the creator as an active member so the private access check passes.
    $group->members()->attach($creator->id, ['role' => 'admin', 'status' => 'active']);

    // Authenticated members should receive a successful response from the Livewire powered route.
    $response = actingAs($creator)->get(route('group.detail', $group));

    $response->assertOk();
    $response->assertSeeLivewire(Show::class);
});

it('blocks non-members from viewing a private group', function (): void {
    // Spin up a group owner and an unrelated visitor to exercise the authorization guard.
    $creator = User::factory()->create();
    $visitor = User::factory()->create();

    // Persist a private group and attach only the creator so the visitor lacks access.
    $group = Group::query()->create([
        'name' => 'Secret Cat Society',
        'slug' => sprintf('secret-cat-society-%s', Str::uuid()),
        'description' => 'Invite-only group for cat lovers.',
        'category_id' => null,
        'visibility' => 'private',
        'creator_id' => $creator->id,
        'location' => 'Hidden Rooftop',
        'rules' => ['No spoilers about upcoming events.'],
    ]);

    $group->members()->attach($creator->id, ['role' => 'admin', 'status' => 'active']);

    // The visitor should be forbidden because the component aborts when membership is missing.
    actingAs($visitor)
        ->get(route('group.detail', $group))
        ->assertForbidden();
});

it('allows authenticated members to leave the group via the component action', function (): void {
    // Prepare a member-owned group so the Livewire action can operate against a persisted record.
    $member = User::factory()->create();
    $group = Group::query()->create([
        'name' => 'Departure Testing Circle',
        'slug' => sprintf('departure-testing-circle-%s', Str::uuid()),
        'description' => 'Ensuring the leaveGroup action detaches memberships.',
        'category_id' => null,
        'visibility' => 'closed',
        'creator_id' => $member->id,
        'location' => 'Testing Atrium',
        'rules' => ['Announce departures politely.'],
    ]);

    // Attach the member so the pivot row exists before we attempt to leave.
    $group->members()->attach($member->id, ['role' => 'admin', 'status' => 'active']);

    // Authenticate and trigger the Livewire action to confirm the redirect target and detach side effect.
    actingAs($member);

    Livewire::test(Show::class, ['group' => $group])
        ->call('leaveGroup')
        ->assertRedirect(route('group.index'));

    // Refresh the group to assert the membership pivot no longer includes the departing user.
    $membershipStillExists = $group->fresh()
        ->members()
        ->where('users.id', $member->id)
        ->exists();

    expect($membershipStillExists)->toBeFalse();
});
