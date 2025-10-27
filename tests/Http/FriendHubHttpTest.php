<?php

use App\Http\Livewire\Common\Friend\Hub;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

uses(RefreshDatabase::class);

it('serves the friend hub component over HTTP when routed directly', function (): void {
    // Register a temporary route that returns the Livewire component for the request.
    Route::middleware('web')->get('/test-friend-hub', Hub::class);

    // Authenticate the visitor so the component can infer the default entity identifier.
    $user = User::factory()->create();
    actingAs($user);

    // Perform the HTTP request and verify the rendered HTML includes the hub container.
    get('/test-friend-hub')
        ->assertOk()
        ->assertSee('friend-hub-container');
});
