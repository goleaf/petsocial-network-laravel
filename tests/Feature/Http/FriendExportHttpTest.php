<?php

use App\Http\Livewire\Common\Friend\Export as FriendExportComponent;
use Livewire\Livewire;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

require_once __DIR__.'/../FriendExportTestHelpers.php';

it('redirects guests who attempt to open the friend export screen', function (): void {
    $response = get(route('friend.export'));

    $response->assertRedirect(route('login'));
});

it('renders the friend export Livewire component for authenticated users', function (): void {
    [$owner] = createFriendExportUsers();

    actingAs($owner);

    // Register the Livewire component alias and container binding expected by the route helper.
    Livewire::component('Common\\Friend\\Export', FriendExportComponent::class);
    app()->bind('Common\\Friend\\Export', fn ($app, $parameters = []) => Livewire::test(FriendExportComponent::class, $parameters)->html());

    $response = get(route('friend.export'));

    $response->assertOk();
    // Verify a key Livewire binding appears so we know the component rendered successfully.
    $response->assertSee('wire:model="exportType"', false);
});

it('exposes the export trigger wiring within the rendered blade view', function (): void {
    [$owner] = createFriendExportUsers();

    actingAs($owner);

    Livewire::component('Common\\Friend\\Export', FriendExportComponent::class);
    app()->bind('Common\\Friend\\Export', fn ($app, $parameters = []) => Livewire::test(FriendExportComponent::class, $parameters)->html());

    $response = get(route('friend.export'));

    // The rendered markup should include the Livewire click binding that initiates exports.
    $response->assertSee('wire:click="export"', false);
});
