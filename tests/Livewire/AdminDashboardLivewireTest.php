<?php

use App\Http\Livewire\Admin\Dashboard;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Livewire;
use function Pest\Laravel\actingAs;

it('handles user management actions through the admin livewire dashboard', function () {
    // Fix the clock to guarantee suspension windows are deterministic.
    Carbon::setTestNow(Carbon::parse('2025-04-15 09:00:00'));

    // Prepare an administrator and a member that will be managed during the test.
    $admin = User::withoutEvents(fn () => User::factory()->create(['role' => 'admin']));
    $member = User::withoutEvents(fn () => User::factory()->create(['email' => 'member@example.com']));
    actingAs($admin);

    // Mount the Livewire component to exercise its interactive methods.
    $component = Livewire::test(Dashboard::class)
        // Confirm the component renders the expected Blade template so UI bindings stay intact.
        ->assertViewIs('livewire.admin.dashboard')
        // Ensure the chart payload is passed to the Blade view for visualisations.
        ->assertViewHas('activityStats', function (array $stats): bool {
            return array_key_exists('users', $stats)
                && array_key_exists('posts', $stats)
                && array_key_exists('activities', $stats);
        });

    // Opening the user modal should store the selected identifier for later use.
    $component->call('viewUser', $member->id)
        ->assertSet('selectedUserId', $member->id)
        ->assertSet('showUserModal', true);

    // Editing a user preloads the existing profile information into editable fields.
    $component->call('editUser', $member->id)
        ->assertSet('editingUserId', $member->id)
        ->assertSet('editName', $member->name)
        ->assertSet('editEmail', $member->email)
        ->assertSet('editRole', $member->role ?? 'user');

    // Saving the edits should persist the updated identity and role assignment.
    $component
        ->set('editName', 'Updated Member')
        ->set('editEmail', 'updated-member@example.com')
        ->set('editRole', 'moderator')
        ->call('updateUser')
        ->assertSet('editingUserId', null);

    // Confirm the member record reflects the updates performed by the component.
    $member->refresh();
    expect($member->name)->toBe('Updated Member');
    expect($member->email)->toBe('updated-member@example.com');
    expect($member->role)->toBe('moderator');

    // Triggering a suspension should record the duration and reason for auditing.
    $component->call('suspendUser', $member->id)
        ->set('suspendDays', null)
        ->set('suspendReason', 'Policy breach in forum threads')
        ->call('confirmSuspend');

    // Validate the suspension timeline and metadata stored on the user.
    $member->refresh();
    expect($member->suspended_at)->not->toBeNull();
    expect($member->suspension_ends_at)->toBeNull();
    expect($member->suspension_reason)->toBe('Policy breach in forum threads');

    // Unsuspending the user should clear all suspension-related columns immediately.
    $component->call('unsuspendUser', $member->id);
    $member->refresh();
    expect($member->suspended_at)->toBeNull();
    expect($member->suspension_ends_at)->toBeNull();
    expect($member->suspension_reason)->toBeNull();

    // Clean up the frozen time state for any subsequent tests.
    Carbon::setTestNow();
});
