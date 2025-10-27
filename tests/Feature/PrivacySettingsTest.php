<?php

use App\Http\Livewire\UserSettings;
use App\Models\User;
use Livewire\Livewire;

/**
 * Feature tests covering privacy presets and runtime enforcement.
 */
it('applies privacy presets to every section', function () {
    // Create a user with a non-standard privacy configuration to ensure presets overwrite each key.
    $user = User::factory()->create([
        'privacy_settings' => array_merge(User::PRIVACY_DEFAULTS, [
            'activity' => 'friends',
            'pets' => 'friends',
        ]),
    ]);

    $this->actingAs($user);

    $component = Livewire::test(UserSettings::class)
        ->call('applyPrivacyPreset', 'private');

    foreach (array_keys(User::PRIVACY_DEFAULTS) as $section) {
        $component->assertSet("privacySettings.{$section}", 'private');
    }

    $component->call('applyPrivacyPreset', 'friends');

    foreach (array_keys(User::PRIVACY_DEFAULTS) as $section) {
        $component->assertSet("privacySettings.{$section}", 'friends');
    }
});

it('ignores invalid privacy presets', function () {
    // Verify that unexpected preset values do not mutate stored visibility rules.
    $user = User::factory()->create([
        'privacy_settings' => array_merge(User::PRIVACY_DEFAULTS, [
            'activity' => 'private',
        ]),
    ]);

    $this->actingAs($user);

    Livewire::test(UserSettings::class)
        ->call('applyPrivacyPreset', 'invalid')
        ->assertSet('privacySettings.activity', 'private');
});

it('forbids viewing activity logs when privacy rules block access', function () {
    // Ensure a visitor without friendship cannot see activity feeds marked as private.
    $owner = User::factory()->create([
        'privacy_settings' => array_merge(User::PRIVACY_DEFAULTS, [
            'activity' => 'private',
        ]),
    ]);

    $viewer = User::factory()->create();

    $response = $this->actingAs($viewer)->get(route('activity', [
        'entity_type' => 'user',
        'entity_id' => $owner->id,
    ]));

    $response->assertForbidden();
});
