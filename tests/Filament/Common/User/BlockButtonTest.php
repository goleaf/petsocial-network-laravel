<?php

use App\Http\Livewire\Common\User\BlockButton;
use App\Models\User;
use Illuminate\Support\Facades\Blade;
use Livewire\Livewire;
use function Pest\Laravel\actingAs;

it('can be embedded within a simulated Filament action view', function (): void {
    // Build a fake Filament-like wrapper that would render the Livewire component inside an action panel.
    $template = <<<'BLADE'
    <div>
        {{-- Simulate Filament including the Livewire component inside an action body. --}}
        <livewire:common.user.block-button :user-id="$userId" />
    </div>
    BLADE;

    // Prepare an authenticated user context so Livewire can evaluate the component logic.
    $blocker = User::factory()->create();
    $blocked = User::factory()->create();
    actingAs($blocker);

    // Render the Blade stub once to ensure the component can bootstrap without real Filament dependencies.
    $rendered = Blade::render($template, ['userId' => $blocked->id]);
    expect($rendered)->toContain('Block');

    // Double-check we can still interact with the component after the synthetic render cycle.
    Livewire::test(BlockButton::class, ['userId' => $blocked->id])
        ->call('toggleBlock')
        ->assertSet('isBlocked', true);
});
