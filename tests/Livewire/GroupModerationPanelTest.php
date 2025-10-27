<?php

use App\Http\Livewire\Group\Moderation\Panel;
use App\Models\Group\Group;
use App\Models\User;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Str;
use Livewire\Livewire;

it('approves pending membership requests', function (): void {
    // Anchor the clock so joined_at assertions can be deterministic.
    Date::setTestNow(now());

    $admin = User::factory()->create();
    $pendingUser = User::factory()->create();

    // Persist a group and attach the admin + pending member in their respective states.
    $group = Group::query()->create([
        'name' => 'Evening Explorers',
        'slug' => sprintf('evening-explorers-%s', Str::uuid()),
        'description' => 'Night walks for pets and their humans.',
        'category_id' => null,
        'visibility' => 'closed',
        'creator_id' => $admin->id,
        'location' => 'Riverwalk',
        'rules' => ['Bring reflective gear.'],
    ]);

    $group->members()->attach($admin->id, ['role' => 'admin', 'status' => 'active', 'joined_at' => now()]);
    $group->members()->attach($pendingUser->id, ['role' => 'member', 'status' => 'pending']);

    $this->actingAs($admin);

    Livewire::test(Panel::class, ['group' => $group])
        ->call('approveMember', $pendingUser->id);

    $approved = $group->fresh()
        ->members()
        ->where('users.id', $pendingUser->id)
        ->first();

    expect($approved->pivot->status)->toBe('active')
        ->and($approved->pivot->joined_at->eq(now()))->toBeTrue();

    // Reset the mocked clock to avoid leaking state into subsequent tests.
    Date::setTestNow();
});

it('denies pending membership requests', function (): void {
    $admin = User::factory()->create();
    $pendingUser = User::factory()->create();

    // Create a group to exercise the moderation denial flow.
    $group = Group::query()->create([
        'name' => 'Trail Testers',
        'slug' => sprintf('trail-testers-%s', Str::uuid()),
        'description' => 'Evaluating the best hiking spots.',
        'category_id' => null,
        'visibility' => 'closed',
        'creator_id' => $admin->id,
        'location' => 'Mountain Base',
        'rules' => ['Share GPS coordinates.'],
    ]);

    $group->members()->attach($admin->id, ['role' => 'admin', 'status' => 'active', 'joined_at' => now()]);
    $group->members()->attach($pendingUser->id, ['role' => 'member', 'status' => 'pending']);

    $this->actingAs($admin);

    Livewire::test(Panel::class, ['group' => $group])
        ->call('denyMember', $pendingUser->id);

    $membershipExists = $group->fresh()
        ->members()
        ->where('users.id', $pendingUser->id)
        ->exists();

    expect($membershipExists)->toBeFalse();
});

it('bans and reinstates active members', function (): void {
    $admin = User::factory()->create();
    $member = User::factory()->create();

    // Create a group where moderation actions will transition statuses.
    $group = Group::query()->create([
        'name' => 'Community Stewards',
        'slug' => sprintf('community-stewards-%s', Str::uuid()),
        'description' => 'Keeping neighbourhood groups thriving.',
        'category_id' => null,
        'visibility' => 'closed',
        'creator_id' => $admin->id,
        'location' => 'Civic Center',
        'rules' => ['Respect each member.'],
    ]);

    $group->members()->attach($admin->id, ['role' => 'admin', 'status' => 'active', 'joined_at' => now()]);
    $group->members()->attach($member->id, ['role' => 'member', 'status' => 'active', 'joined_at' => now()]);

    $this->actingAs($admin);

    Livewire::test(Panel::class, ['group' => $group])
        ->call('banMember', $member->id)
        ->call('unbanMember', $member->id);

    $pivot = $group->fresh()
        ->members()
        ->where('users.id', $member->id)
        ->first()
        ->pivot;

    expect($pivot->status)->toBe('active');
});
