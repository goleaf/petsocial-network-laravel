<?php

use App\Http\Livewire\Group\Details\Show;
use App\Models\Group\Category;
use App\Models\Group\Group;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * Lightweight stub mimicking the fill contract Filament forms expect.
 */
class FakeFilamentForm
{
    public array $received = [];

    public function fill(array $data): void
    {
        // Store the incoming payload so assertions can examine the structure.
        $this->received = $data;
    }
}

/**
 * Filament-focused assertions ensuring the component exposes cohesive form state.
 */
it('provides structured data compatible with filament form builders', function (): void {
    // Create the baseline data models that the component needs to hydrate itself.
    $creator = User::factory()->create();
    $category = Category::query()->create([
        'name' => 'Community Hubs',
        'slug' => sprintf('community-hubs-%s', Str::uuid()),
    ]);

    $group = Group::query()->create([
        'name' => 'Filament Ready Collective',
        'slug' => sprintf('filament-ready-collective-%s', Str::uuid()),
        'description' => 'Group for testing administrative tooling integrations.',
        'category_id' => $category->id,
        'visibility' => 'open',
        'creator_id' => $creator->id,
        'location' => 'Innovation Lab',
        'rules' => ['Document every change.'],
    ]);

    // Initialise the component so the data-loading helper runs before exporting form state.
    $component = new Show();
    $component->group = $group->fresh();
    $component->loadGroupData();

    // Collect the core fields a Filament form would expect when bootstrapping state.
    $formState = [
        'name' => $component->name,
        'description' => $component->description,
        'category' => $component->category,
        'visibility' => $component->visibility,
        'location' => $component->location,
        'rules' => $component->rules,
    ];

    $form = new FakeFilamentForm();
    $form->fill($formState);

    // Ensure the captured data includes every key and maintains array types for repeater fields.
    expect($form->received)->toHaveKeys(['name', 'description', 'category', 'visibility', 'location', 'rules'])
        ->and($form->received['rules'])->toBeArray()
        ->and($form->received['name'])->toBe($group->name)
        ->and($form->received['description'])->toBe($group->description);
});
