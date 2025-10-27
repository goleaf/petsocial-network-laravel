<?php

use App\Http\Livewire\Common\Friend\Hub;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\View\View;

uses(RefreshDatabase::class);

it('exposes a renderable view that Filament panels can embed', function (): void {
    // Provision a user record so the component can resolve the entity when rendering.
    $user = User::factory()->create();

    // Manually instantiate the component to mirror how Filament would include it as a widget.
    $component = new Hub();
    $component->entityType = 'user';
    $component->entityId = $user->id;
    $component->stats = [
        'total_friends' => 0,
        'pending_sent' => 0,
        'pending_received' => 0,
        'recent_activity' => 0,
        'categories' => [],
    ];

    // Render the component and confirm Filament-compatible view metadata is returned.
    $view = $component->render();

    // Validate the view instance and payload that Filament would receive for embedding.
    expect($view)->toBeInstanceOf(View::class)
        ->and($view->name())->toBe('livewire.common.friend.hub')
        ->and($view->getData()['entity']->is($user))->toBeTrue();
});
