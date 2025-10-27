<?php

use App\Http\Livewire\Group\Forms\Create;

/**
 * Filament-centric compatibility checks for the group creation component.
 */
test('group create component rules translate into a Filament-style schema blueprint', function () {
    // Reflect the component rules so we can map them into a Filament-friendly structure.
    $component = new Create();
    $reflection = new ReflectionClass($component);
    $property = $reflection->getProperty('rules');
    $property->setAccessible(true);
    $rules = $property->getValue($component);

    // Fake form object that mimics Filament's fluent schema configuration.
    $fakeForm = new class {
        public array $schema = [];

        public function schema(array $schema): self
        {
            $this->schema = $schema;

            return $this;
        }
    };

    $schema = [];

    foreach ($rules as $field => $ruleString) {
        // Split the rule definitions so we can derive metadata for the pseudo Filament fields.
        $parts = explode('|', $ruleString);
        $componentType = match ($field) {
            'visibility', 'categoryId' => 'Select',
            'coverImage', 'icon' => 'FileUpload',
            default => 'TextInput',
        };
        $max = null;

        foreach ($parts as $part) {
            if (str_starts_with($part, 'max:')) {
                $max = (int) str_replace('max:', '', $part);
            }
        }

        $schema[] = [
            'field' => $field,
            'component' => $componentType,
            'required' => in_array('required', $parts, true),
            'acceptsImages' => in_array($componentType, ['FileUpload'], true),
            'max' => $max,
        ];
    }

    $fakeForm->schema($schema);

    // The schema should represent every rule entry and preserve the derived metadata we care about.
    expect($fakeForm->schema)->toHaveCount(count($rules));
    expect(collect($fakeForm->schema)->firstWhere('field', 'name')['required'])->toBeTrue();
    expect(collect($fakeForm->schema)->firstWhere('field', 'coverImage')['component'])->toBe('FileUpload');
    expect(collect($fakeForm->schema)->firstWhere('field', 'visibility')['component'])->toBe('Select');
});
