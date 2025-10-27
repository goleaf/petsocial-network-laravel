<?php

use App\Http\Livewire\Common\Friend\Export as FriendExportComponent;
use Livewire\Livewire;
use function Pest\Laravel\actingAs;

require_once __DIR__.'/FriendExportTestHelpers.php';

it('toggles friend selection and resets when search changes', function (): void {
    [$owner, $friend] = createFriendExportUsers();

    actingAs($owner);

    Livewire::test(FriendExportComponent::class, ['entityType' => 'user', 'entityId' => $owner->id])
        // Select every friend to ensure the helper collects the cached IDs correctly.
        ->call('toggleSelectAll')
        ->assertSet('selectAll', true)
        ->assertSet('selectedFriends', [$friend->id])
        // Updating the search term should clear any selection to avoid mismatched exports.
        ->set('search', 'Jordan')
        ->assertSet('selectAll', false)
        ->assertSet('selectedFriends', []);
});

it('flashes an error when export is triggered without any selection', function (): void {
    [$owner] = createFriendExportUsers();

    actingAs($owner);

    Livewire::test(FriendExportComponent::class, ['entityType' => 'user', 'entityId' => $owner->id])
        ->set('exportFormat', 'json')
        ->call('export')
        ->assertReturned(null);
});

it('supports exporting follower lists when the export type changes', function (): void {
    [$owner] = createFriendExportUsers();
    $follower = attachExportFollower($owner);

    // Confirm the follower relationship contains the new user before exercising Livewire behaviour.
    expect($owner->followers()->pluck('users.id')->toArray())->toContain($follower->id);

    $componentInstance = app(FriendExportComponent::class);
    $componentInstance->mount('user', $owner->id);
    $componentInstance->exportType = 'followers';

    $users = $componentInstance->getUsersByType();

    expect($users)->toHaveCount(1);
    expect($users->first()->name)->toBe($follower->name);

    actingAs($owner);

    $test = Livewire::test(FriendExportComponent::class, ['entityType' => 'user', 'entityId' => $owner->id]);

    $test->set('exportType', 'followers');
    $test->call('toggleSelectAll');

    $ids = $test->get('selectedFriends');

    expect($ids)->toBeArray();
    expect($ids)->toHaveCount(1);
    expect($componentInstance->getUsersByType()->first()->name)->toBe($follower->name);
});
