<?php

use App\Http\Livewire\Admin\Dashboard;
use App\Models\User;
use Livewire\Livewire;
use function Pest\Laravel\actingAs;

it('exposes role metadata that filament forms can consume without reshaping', function () {
    // Authenticate as an administrator to ensure the component boots with full context.
    $admin = User::withoutEvents(fn () => User::factory()->create(['role' => 'admin']));
    actingAs($admin);

    // Mount the Livewire component exactly how a Filament widget would during rendering.
    $component = Livewire::test(Dashboard::class);

    // Filament select components expect the identifier list and label map provided by these helpers.
    expect($component->get('availableRoles'))->toBe(User::availableRoles());
    expect($component->get('roleOptions'))->toBe(User::roleOptions());
});
