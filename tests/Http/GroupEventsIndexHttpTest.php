<?php

use App\Models\Group\Category;
use App\Models\Group\Group;
use App\Models\User;
use Illuminate\Support\Str;

it('requires authentication to reach the group events surface', function (): void {
    prepareTestDatabase();
    $category = Category::query()->create([
        'name' => 'Trails',
        'slug' => sprintf('trails-%s', Str::uuid()),
    ]);
    $group = Group::query()->create([
        'name' => 'Trail Stewards',
        'slug' => sprintf('trail-stewards-%s', Str::uuid()),
        'description' => 'Organising regular clean-up hikes.',
        'category_id' => $category->id,
        'visibility' => Group::VISIBILITY_OPEN,
        'creator_id' => User::factory()->create()->id,
        'location' => 'Greenway',
        'rules' => ['Pack out what you pack in.'],
    ]);

    $this->get(route('group.events', $group))->assertRedirect(route('login'));
});

it('returns forbidden for viewers without group access', function (): void {
    prepareTestDatabase();
    $owner = User::factory()->create();
    $outsider = User::factory()->create();
    $category = Category::query()->create([
        'name' => 'Training',
        'slug' => sprintf('training-%s', Str::uuid()),
    ]);

    $group = Group::query()->create([
        'name' => 'Secret Training Grounds',
        'slug' => sprintf('secret-training-grounds-%s', Str::uuid()),
        'description' => 'Invite-only agility sessions.',
        'category_id' => $category->id,
        'visibility' => Group::VISIBILITY_SECRET,
        'creator_id' => $owner->id,
        'location' => 'Undisclosed',
        'rules' => ['Keep locations private.'],
    ]);

    $group->members()->attach($owner->id, ['role' => 'admin', 'status' => 'active']);

    $this->actingAs($outsider)
        ->get(route('group.events', $group))
        ->assertForbidden();
});
