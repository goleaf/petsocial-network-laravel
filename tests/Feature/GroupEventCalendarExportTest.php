<?php

use App\Models\Group\Event;
use App\Models\Group\Group;
use App\Models\User;
use Illuminate\Support\Carbon;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

it('allows group members to download an ics export for published events', function (): void {
    $member = User::factory()->create();

    $group = Group::create([
        'name' => 'Neighborhood Paws',
        'description' => 'Community focused events for local pet lovers.',
        'visibility' => Group::VISIBILITY_OPEN,
        'creator_id' => $member->id,
        'rules' => [],
    ]);

    // Promote the member to an active participant so they inherit viewing privileges.
    $group->members()->attach($member->id, [
        'role' => 'admin',
        'status' => 'active',
        'joined_at' => now(),
    ]);

    $event = Event::create([
        'title' => 'Morning Pack Walk',
        'description' => "Bring water and comfortable shoes for a relaxed park loop.",
        'group_id' => $group->id,
        'user_id' => $member->id,
        'start_date' => Carbon::now()->addDays(2),
        'end_date' => Carbon::now()->addDays(2)->addHours(2),
        'location' => 'Central Bark Park',
        'is_online' => false,
        'is_published' => true,
    ]);

    actingAs($member);

    $response = get(route('group.event.export', ['group' => $group, 'event' => $event]));

    // Members should receive a downloadable ICS payload with the expected headers.
    $response->assertOk();
    $response->assertHeader('content-type', 'text/calendar; charset=utf-8');
    expect($response->getContent())->toContain('BEGIN:VCALENDAR');
    expect($response->getContent())->toContain('SUMMARY:Morning Pack Walk');
    expect($response->getContent())->toContain('LOCATION:Central Bark Park');
});

it('blocks non members from exporting events for secret groups', function (): void {
    $owner = User::factory()->create();
    $outsider = User::factory()->create();

    $group = Group::create([
        'name' => 'Secret Cat Council',
        'description' => 'Invite-only gathering for feline caretakers.',
        'visibility' => Group::VISIBILITY_SECRET,
        'creator_id' => $owner->id,
        'rules' => [],
    ]);

    // Ensure the owner counts as a member so the event can be created legitimately.
    $group->members()->attach($owner->id, [
        'role' => 'admin',
        'status' => 'active',
        'joined_at' => now(),
    ]);

    $event = Event::create([
        'title' => 'Moonlight Planning Session',
        'description' => 'Plot the next enrichment initiative.',
        'group_id' => $group->id,
        'user_id' => $owner->id,
        'start_date' => Carbon::now()->addWeek(),
        'location' => 'Hidden Garden Loft',
        'is_online' => false,
        'is_published' => true,
    ]);

    actingAs($outsider);

    // Outsiders should not be able to download sensitive group events.
    get(route('group.event.export', ['group' => $group, 'event' => $event]))->assertForbidden();
});

it('renders the event detail page for authorised members', function (): void {
    $member = User::factory()->create();

    $group = Group::create([
        'name' => 'Weekend Explorers',
        'description' => 'Outdoor adventures every Saturday morning.',
        'visibility' => Group::VISIBILITY_OPEN,
        'creator_id' => $member->id,
        'rules' => [],
    ]);

    $group->members()->attach($member->id, [
        'role' => 'member',
        'status' => 'active',
        'joined_at' => now(),
    ]);

    $event = Event::create([
        'title' => 'Trail Clean-Up',
        'description' => 'Help tidy the north trail and enjoy coffee afterwards.',
        'group_id' => $group->id,
        'user_id' => $member->id,
        'start_date' => Carbon::now()->addDays(3),
        'location' => 'Riverbend Trailhead',
        'is_online' => false,
        'is_published' => true,
    ]);

    actingAs($member);

    $response = get(route('group.event', ['group' => $group, 'event' => $event]));

    // The detail view should surface contextual information for the attendee.
    $response->assertOk();
    $response->assertSee('Trail Clean-Up');
    $response->assertSee('Riverbend Trailhead');
    $response->assertSee('Download iCal');
});
