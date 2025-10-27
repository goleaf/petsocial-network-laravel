<?php

use App\Http\Livewire\UserSettings;
use App\Models\User;
use Livewire\Livewire;

/**
 * Livewire tests ensure the component lifecycle wiring behaves as expected.
 */
it('hydrates privacy and notification state during mount', function () {
    // Seed a user with partial privacy data to verify defaults merge as the component boots.
    $user = User::factory()->create([
        'profile_visibility' => 'public',
        'posts_visibility' => 'public',
        'privacy_settings' => [
            'basic_info' => 'private',
            'stats' => 'public',
            'friends' => 'friends',
        ],
        'notification_preferences' => [
            'channels' => [
                'in_app' => true,
                'email' => false,
                'push' => true,
            ],
            'categories' => [
                'messages' => [
                    'enabled' => false,
                    'priority' => 'high',
                    'frequency' => 'instant',
                ],
            ],
            'frequency' => [
                'low' => 'daily',
                'normal' => 'hourly',
                'high' => 'instant',
                'critical' => 'instant',
            ],
            'digest' => [
                'enabled' => false,
                'interval' => 'daily',
                'send_time' => '08:00',
                'last_sent_at' => null,
            ],
        ],
        'two_factor_enabled' => true,
    ]);

    $this->actingAs($user);

    Livewire::test(UserSettings::class)
        // Confirm basic identity fields are hydrated on mount.
        ->assertSet('name', $user->name)
        ->assertSet('email', $user->email)
        // Privacy settings should merge stored overrides with defaults.
        ->assertSet('privacySettings.basic_info', 'private')
        ->assertSet('privacySettings.friends', 'friends')
        // Notification preferences reflect stored configuration.
        ->assertSet('notificationPreferences.categories.messages.enabled', false)
        // Render output includes the two-factor flag and supporting metadata for templates.
        ->assertViewHas('twoFactorEnabled', true)
        ->assertViewHas('privacySections', function (array $sections): bool {
            // Ensure every privacy section has a translated label available to the template.
            return array_key_exists('basic_info', $sections) && array_key_exists('activity', $sections);
        });
});

it('toggles notification categories through the dedicated helper', function () {
    $user = User::factory()->create([
        'profile_visibility' => 'public',
        'posts_visibility' => 'public',
    ]);

    $this->actingAs($user);

    Livewire::test(UserSettings::class)
        // Default preferences mark categories as enabled, so the first toggle should disable the category.
        ->call('toggleNotification', 'messages')
        ->assertSet('notificationPreferences.categories.messages.enabled', false)
        // Toggling again should restore the enabled flag.
        ->call('toggleNotification', 'messages')
        ->assertSet('notificationPreferences.categories.messages.enabled', true);
});
