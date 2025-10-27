<?php

use App\Models\Group\Category;
use App\Models\Group\Group;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

it('displays scheduled events for group members', function (): void {
    prepareTestDatabase();
    // Assemble a group with one published event so the page has content to render.
    $member = User::factory()->create();
    $category = Category::query()->create([
        'name' => 'Climbing',
        'slug' => sprintf('climbing-%s', Str::uuid()),
    ]);
    $group = Group::query()->create([
        'name' => 'Community Climbers',
        'slug' => sprintf('community-climbers-%s', Str::uuid()),
        'description' => 'Sharing safe climbing routes for pets and humans.',
        'category_id' => $category->id,
        'visibility' => Group::VISIBILITY_OPEN,
        'creator_id' => $member->id,
        'location' => 'Downtown Hub',
        'rules' => ['Respect harness guidelines.'],
    ]);

    $group->members()->attach($member->id, ['role' => 'admin', 'status' => 'active']);

    $group->events()->create([
        'title' => 'Rooftop Warmup',
        'description' => 'Stretching and grip-strength exercises for a confident climb.',
        'user_id' => $member->id,
        'start_date' => Carbon::now()->addDays(5),
        'end_date' => Carbon::now()->addDays(5)->addHours(1),
        'location' => 'Skyline Center',
        'is_online' => false,
        'is_published' => true,
    ]);

    $this->actingAs($member)
        ->get(route('group.events', $group))
        ->assertOk()
        ->assertSeeText('Rooftop Warmup')
        ->assertSeeText('Skyline Center');
});
