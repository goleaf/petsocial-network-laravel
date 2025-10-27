<?php

use App\Http\Livewire\UserSettings;
use App\Models\User;

/**
 * Unit tests validate the internal helpers on the UserSettings component.
 */
class UserSettingsTestHarness extends UserSettings
{
    /**
     * Expose the protected privacy section mapping for assertion clarity.
     */
    public function exposedPrivacySections(): array
    {
        return $this->privacySections();
    }

    /**
     * Expose the preset definitions so the test can reason about the structure.
     */
    public function exposedPrivacyPresets(): array
    {
        return $this->privacyPresets();
    }
}

it('describes privacy sections and presets for downstream consumers', function () {
    // Instantiate the harness to call protected helpers without booting Livewire runtime.
    $component = new UserSettingsTestHarness();

    $sections = $component->exposedPrivacySections();
    $presets = $component->exposedPrivacyPresets();

    // Ensure each configured privacy section aligns with the constants on the User model.
    expect(array_keys($sections))->toEqual(array_keys(User::PRIVACY_DEFAULTS));

    // Confirm the preset map contains friendly labels for every supported visibility level.
    expect($presets)->toMatchArray([
        'public' => __('common.privacy_preset_public'),
        'friends' => __('common.privacy_preset_friends'),
        'private' => __('common.privacy_preset_private'),
    ]);
});

it('applies privacy presets and records feedback for the UI', function () {
    $component = new UserSettings();
    $component->privacySettings = User::PRIVACY_DEFAULTS;

    // Apply the private preset and ensure each section adopts the preset value.
    $component->applyPrivacyPreset('private');

    foreach (array_keys(User::PRIVACY_DEFAULTS) as $section) {
        expect($component->privacySettings[$section])->toBe('private');
    }

    // The notice should reflect the translated label for the preset that was applied.
    expect($component->privacyPresetNotice)->toBe(__('common.privacy_preset_applied', [
        'preset' => __('common.privacy_preset_private'),
    ]));

    // Attempting to apply an unknown preset should leave the configuration untouched.
    $component->privacyPresetNotice = null;
    $component->privacySettings = User::PRIVACY_DEFAULTS;
    $component->applyPrivacyPreset('unsupported');

    expect($component->privacySettings)->toMatchArray(User::PRIVACY_DEFAULTS);
    expect($component->privacyPresetNotice)->toBeNull();
});
