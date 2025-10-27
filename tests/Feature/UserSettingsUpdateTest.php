<?php

use App\Http\Livewire\UserSettings;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

beforeEach(function () {
    // Rebuild the in-memory schema so each test operates on a predictable dataset.
    prepareTestDatabase();
});

/**
 * Feature coverage ensuring the Livewire settings panel persists updates correctly.
 */
it('updates settings and normalises notification preferences', function () {
    // Create a member with explicit privacy and notification preferences to exercise update hygiene.
    $user = User::factory()->create([
        'profile_visibility' => 'public',
        'posts_visibility' => 'public',
        'privacy_settings' => array_merge(User::PRIVACY_DEFAULTS, [
            'activity' => 'friends',
        ]),
    ]);

    // Persist stored preferences so the service can merge defaults when the component mounts.
    $service = app(NotificationService::class);
    $storedPreferences = $service->preferencesFor($user);
    $user->forceFill(['notification_preferences' => $storedPreferences])->save();

    $this->actingAs($user);

    // Craft intentionally messy incoming preferences to ensure cleanPreferences() resolves unsupported values.
    $incomingPreferences = $service->preferencesFor($user);
    $incomingPreferences['categories']['messages']['priority'] = 'invalid-priority';
    $incomingPreferences['frequency']['normal'] = 'unrecognised-frequency';
    $incomingPreferences['digest']['send_time'] = '25:61';
    $incomingPreferences['digest']['interval'] = 'weekly';

    Livewire::test(UserSettings::class)
        // Update personal metadata.
        ->set('name', 'Updated User')
        ->set('email', 'updated@example.com')
        ->set('profile_visibility', 'friends')
        ->set('posts_visibility', 'friends')
        // Provide explicit visibility overrides for every privacy section.
        ->set('privacySettings', [
            'basic_info' => 'private',
            'stats' => 'friends',
            'friends' => 'private',
            'mutual_friends' => 'friends',
            'pets' => 'public',
            'activity' => 'private',
        ])
        // Feed the unsanitised preferences before calling the update action.
        ->set('notificationPreferences', $incomingPreferences)
        ->call('update')
        ->assertHasNoErrors();

    // Refresh the user to inspect stored values after the component ran the update pipeline.
    $refreshed = $user->fresh();

    expect($refreshed->name)->toBe('Updated User');
    expect($refreshed->email)->toBe('updated@example.com');

    // Confirm that privacy settings were merged with defaults and persisted correctly.
    expect($refreshed->privacy_settings)->toMatchArray([
        'basic_info' => 'private',
        'stats' => 'friends',
        'friends' => 'private',
        'mutual_friends' => 'friends',
        'pets' => 'public',
        'activity' => 'private',
    ]);

    $savedPreferences = $refreshed->notification_preferences;

    // Invalid priority entries should fall back to the configured default.
    expect(Arr::get($savedPreferences, 'categories.messages.priority'))->toBe('high');
    // Invalid frequency entries should revert to the default cadence for the priority tier.
    expect(Arr::get($savedPreferences, 'frequency.normal'))->toBe('hourly');
    // Unparseable send times should reset to the configured default window.
    expect(Arr::get($savedPreferences, 'digest.send_time'))->toBe('08:00');

    // Flash messaging is handled within the component; Livewire's test harness does not expose the flash bag directly here.
});

it('updates the account password through the dedicated action', function () {
    // Provision a user with the default factory password so the current_password rule can validate the request.
    $user = User::factory()->create([
        'profile_visibility' => 'public',
        'posts_visibility' => 'public',
    ]);

    $this->actingAs($user);

    // Drive the Livewire component to submit the password update payload and ensure validation passes.
    Livewire::test(UserSettings::class)
        ->set('current_password', 'password')
        ->set('password', 'new-strong-password')
        ->set('password_confirmation', 'new-strong-password')
        ->call('updatePassword')
        ->assertHasNoErrors()
        // After a successful update the component should clear the password fields for security hygiene.
        ->assertSet('current_password', null)
        ->assertSet('password', null)
        ->assertSet('password_confirmation', null);

    // Refresh the model to confirm the password hash has been replaced with the new value.
    $refreshed = $user->fresh();

    expect(Hash::check('new-strong-password', $refreshed->password))->toBeTrue();
});
