<?php

use App\Http\Livewire\Common\Friend\Hub;

it('updates the active tab when requested', function (): void {
    // Instantiate the component without touching the database to verify pure state changes.
    $component = new Hub();

    // Switch the active tab and assert that the property reflects the new selection.
    $component->setActiveTab('friends');

    // Confirm the component now targets the requested tab name.
    expect($component->activeTab)->toBe('friends');
});
