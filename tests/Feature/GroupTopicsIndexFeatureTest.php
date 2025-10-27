<?php

use App\Http\Livewire\Group\Topics\Index;
use App\Models\Group\Group;
use App\Models\Group\Topic;
use App\Models\User;
use Livewire\Livewire;

it('allows group administrators to toggle topic pinning through the Livewire action', function () {
    // Create an administrator who will manage topics for the group.
    $admin = User::factory()->create();

    // Persist the group with an explicit slug so route model binding remains predictable.
    $group = Group::create([
        'name' => 'Feature Test Group',
        'slug' => 'feature-test-group',
        'description' => 'Group dedicated to feature testing.',
        'visibility' => 'open',
        'creator_id' => $admin->id,
        'is_active' => true,
    ]);

    // Grant the administrator membership privileges inside the group context.
    $group->members()->attach($admin->id, [
        'role' => 'admin',
        'status' => 'active',
        'joined_at' => now(),
    ]);

    // Seed a topic that can be pinned or unpinned during the exercise.
    $topic = Topic::create([
        'group_id' => $group->id,
        'user_id' => $admin->id,
        'title' => 'Initial Topic',
        'content' => 'Administrators can decide which conversations stay visible.',
    ]);

    // Authenticate as the administrator to satisfy authorization expectations.
    $this->actingAs($admin);

    // Execute the pinTopic action and ensure the database reflects the change.
    Livewire::test(Index::class, ['group' => $group])
        ->call('pinTopic', $topic->id);

    expect($topic->fresh()->is_pinned)->toBeTrue();
});
