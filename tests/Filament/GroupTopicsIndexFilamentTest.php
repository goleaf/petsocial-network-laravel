<?php

use App\Http\Livewire\Group\Topics\Index;
use App\Models\Group\Group;
use App\Models\Group\Topic;
use App\Models\User;
use Livewire\Livewire;

it('pins multiple topics via the bulk action pipeline used by Filament-style tables', function () {
    // Prepare an administrator who mirrors the elevated privileges in a Filament dashboard.
    $admin = User::factory()->create();

    // Persist the group and ensure slug stability for predictable bindings.
    $group = Group::create([
        'name' => 'Filament Ready Group',
        'slug' => 'filament-ready-group',
        'description' => 'Group configured to validate bulk pinning.',
        'visibility' => 'open',
        'creator_id' => $admin->id,
        'is_active' => true,
    ]);

    // Register the admin as an active member to unlock moderation routes.
    $group->members()->attach($admin->id, [
        'role' => 'admin',
        'status' => 'active',
        'joined_at' => now(),
    ]);

    // Seed two topics that will be targeted by the bulk action selection list.
    $firstTopic = Topic::create([
        'group_id' => $group->id,
        'user_id' => $admin->id,
        'title' => 'Schedule Meetup',
        'content' => 'Coordinate the next community hangout.',
    ]);

    $secondTopic = Topic::create([
        'group_id' => $group->id,
        'user_id' => $admin->id,
        'title' => 'Share Adoption Stories',
        'content' => 'Collect uplifting adoption updates from members.',
    ]);

    // Authenticate to mimic the Filament admin session context.
    $this->actingAs($admin);

    // Execute the bulk pin action and assert both topics persist with the pinned flag enabled.
    Livewire::test(Index::class, ['group' => $group])
        ->set('selectedTopics', [$firstTopic->id, $secondTopic->id])
        ->set('bulkAction', 'pin')
        ->call('executeBulkAction');

    expect($firstTopic->fresh()->is_pinned)->toBeTrue()
        ->and($secondTopic->fresh()->is_pinned)->toBeTrue();
});
