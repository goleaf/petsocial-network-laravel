<?php

use App\Models\Group\Category;
use App\Models\Group\Group;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

it('detects when an event has reached its capacity limit', function (): void {
    prepareTestDatabase();
    // Establish a group with a capped event to exercise the capacity helper.
    $organiser = User::factory()->create();
    $attendee = User::factory()->create();
    $category = Category::query()->create([
        'name' => 'Workshops',
        'slug' => sprintf('workshops-%s', Str::uuid()),
    ]);

    $group = Group::query()->create([
        'name' => 'Capacity Checkers',
        'slug' => sprintf('capacity-checkers-%s', Str::uuid()),
        'description' => 'Testing RSVP restrictions for gatherings.',
        'category_id' => $category->id,
        'visibility' => Group::VISIBILITY_OPEN,
        'creator_id' => $organiser->id,
        'location' => 'Studio A',
        'rules' => ['Respect guest limits.'],
    ]);

    $group->members()->attach($organiser->id, ['role' => 'admin', 'status' => 'active']);
    $group->members()->attach($attendee->id, ['role' => 'member', 'status' => 'active']);

    $event = $group->events()->create([
        'title' => 'Micro Workshop',
        'description' => 'Hands-on training with limited seats.',
        'user_id' => $organiser->id,
        'start_date' => Carbon::now()->addDay(),
        'end_date' => Carbon::now()->addDay()->addHour(),
        'location' => 'Studio A',
        'is_online' => false,
        'max_attendees' => 1,
        'is_published' => true,
    ]);

    $event->attendees()->attach($attendee->id, ['status' => 'going', 'reminder_set' => false]);

    expect($event->fresh()->isAtCapacity())->toBeTrue();
});
