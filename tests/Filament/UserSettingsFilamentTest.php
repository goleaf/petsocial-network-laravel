<?php

use App\Http\Livewire\UserSettings;
use App\Models\User;

/**
 * Filament-oriented tests validate that component data can be mapped to Filament form schemas.
 */
class UserSettingsFilamentHarness extends UserSettings
{
    /**
     * Provide access to the protected presets helper for schema transformation tests.
     */
    public function presetOptions(): array
    {
        return $this->privacyPresets();
    }

    /**
     * Provide access to the protected sections helper for schema transformation tests.
     */
    public function sectionLabels(): array
    {
        return $this->privacySections();
    }
}

it('prepares preset options compatible with Filament select components', function () {
    // The harness lets us transform preset metadata into the structures Filament expects.
    $component = new UserSettingsFilamentHarness();

    $options = collect($component->presetOptions())->map(function (string $label, string $value) {
        // Mimic Filament\Forms\Components\Select option structures (value/label pairs).
        return [
            'value' => $value,
            'label' => $label,
        ];
    })->values()->all();

    foreach ($options as $option) {
        // Every option should expose both a machine value and a translated label.
        expect($option)->toHaveKeys(['value', 'label']);
        expect(in_array($option['value'], User::PRIVACY_VISIBILITY_OPTIONS, true))->toBeTrue();
    }
});

it('builds toggle definitions that map privacy sections to Filament form fields', function () {
    $component = new UserSettingsFilamentHarness();

    $fields = collect($component->sectionLabels())->map(function (string $label, string $section) {
        // Simulate Filament toggle definitions where each section maps to a boolean switch or select input.
        return [
            'name' => "privacySettings.{$section}",
            'label' => $label,
        ];
    })->values();

    // Ensure each privacy section is represented exactly once in the generated schema definition.
    expect($fields->pluck('name')->all())->toEqual(array_map(
        fn (string $section) => "privacySettings.{$section}",
        array_keys(User::PRIVACY_DEFAULTS)
    ));
});
