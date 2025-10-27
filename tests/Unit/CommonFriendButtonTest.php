<?php

use App\Http\Livewire\Common\Friend\Button;
use App\Models\User;

beforeEach(function () {
    // Ensure the unit-level assertions have access to the shared schema and factories.
    prepareTestDatabase();
});

it('toggles the dropdown visibility flag locally', function () {
    // Instantiate the component directly to validate the lightweight toggle logic.
    $component = app(Button::class);

    // Ensure the dropdown becomes visible after the first toggle call.
    $component->showDropdown = false;
    $component->toggleDropdown();
    expect($component->showDropdown)->toBeTrue();

    // A subsequent toggle should collapse the dropdown again for proper UX behaviour.
    $component->toggleDropdown();
    expect($component->showDropdown)->toBeFalse();
});

it('renders the associated blade view with hydrated entity data', function () {
    // Create the owning entity and the target user so render() can resolve both models.
    $entity = User::factory()->create();
    $target = User::factory()->create();

    // Manually configure the component context to mirror a mounted instance.
    $component = app(Button::class);
    $component->entityType = 'user';
    $component->entityId = $entity->id;
    $component->targetId = $target->id;

    // Render the component and confirm the expected Livewire blade view is returned.
    $view = $component->render();

    expect($view->name())->toBe('livewire.common.friend.button')
        ->and($view->getData()['entity']->is($entity))->toBeTrue()
        ->and($view->getData()['target']->is($target))->toBeTrue();
});
