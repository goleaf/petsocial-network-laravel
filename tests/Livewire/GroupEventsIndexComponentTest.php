<?php

use App\Http\Livewire\Group\Events\Index as GroupEventsIndex;
use App\Models\Group\Category;
use App\Models\Group\Group;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Livewire\Livewire;

use function Pest\Laravel\assertDatabaseHas;

it('allows group administrators to schedule new events', function (): void {
    prepareTestDatabase();
    // Provision a group and elevate the acting user to the administrator role.
    $admin = User::factory()->create();
    $category = Category::query()->create([
        'name' => 'Adventures',
        'slug' => sprintf('adventures-%s', Str::uuid()),
    ]);
    $group = Group::query()->create([
        'name' => 'Adventure Buddies',
        'slug' => sprintf('adventure-buddies-%s', Str::uuid()),
        'description' => 'Organising outdoor meetups for adventurous pets and people.',
        'category_id' => $category->id,
        'visibility' => Group::VISIBILITY_OPEN,
        'creator_id' => $admin->id,
        'location' => 'Global',
        'rules' => ['Share event recaps.'],
    ]);

    $group->members()->attach($admin->id, ['role' => 'admin', 'status' => 'active']);

    $this->actingAs($admin);

    $start = Carbon::now()->addDays(3)->format('Y-m-d\TH:i');
    $end = Carbon::now()->addDays(3)->addHours(2)->format('Y-m-d\TH:i');

    Livewire::test(GroupEventsIndex::class, ['group' => $group])
        ->set('showEventModal', true)
        ->set('title', 'Trail Exploration')
        ->set('description', 'Pack a leash and water bowl for a two-hour scenic hike.')
        ->set('startDate', $start)
        ->set('endDate', $end)
        ->set('location', 'Sunset Canyon')
        ->set('locationUrl', 'https://maps.example.com/sunset-canyon')
        ->set('isOnline', false)
        ->set('onlineMeetingUrl', null)
        ->set('maxAttendees', 25)
        ->set('isPublished', true)
        ->call('saveEvent')
        ->assertSet('showEventModal', false);

    assertDatabaseHas('group_events', [
        'group_id' => $group->id,
        'title' => 'Trail Exploration',
        'location' => 'Sunset Canyon',
        'max_attendees' => 25,
    ]);
});

it('lets active members update their rsvp preferences', function (): void {
    prepareTestDatabase();
    // Create an event and attach both an administrator and a standard member.
    $admin = User::factory()->create();
    $member = User::factory()->create();
    $category = Category::query()->create([
        'name' => 'Education',
        'slug' => sprintf('education-%s', Str::uuid()),
    ]);

    $group = Group::query()->create([
        'name' => 'Learning League',
        'slug' => sprintf('learning-league-%s', Str::uuid()),
        'description' => 'Weekly workshops for pets and their people.',
        'category_id' => $category->id,
        'visibility' => Group::VISIBILITY_OPEN,
        'creator_id' => $admin->id,
        'location' => 'Hybrid',
        'rules' => ['Respect instructor guidance.'],
    ]);

    $group->members()->attach($admin->id, ['role' => 'admin', 'status' => 'active']);
    $group->members()->attach($member->id, ['role' => 'member', 'status' => 'active']);

    $event = $group->events()->create([
        'title' => 'Clicker Training 101',
        'description' => 'Positive reinforcement basics for new trainers.',
        'user_id' => $admin->id,
        'start_date' => Carbon::now()->addWeek(),
        'end_date' => Carbon::now()->addWeek()->addHours(1),
        'location' => 'Community Center',
        'is_online' => false,
        'is_published' => true,
    ]);

    $this->actingAs($member);

    Livewire::test(GroupEventsIndex::class, ['group' => $group])
        ->call('setRsvp', $event->id, 'going');

    assertDatabaseHas('group_event_attendees', [
        'group_event_id' => $event->id,
        'user_id' => $member->id,
        'status' => 'going',
    ]);
});
