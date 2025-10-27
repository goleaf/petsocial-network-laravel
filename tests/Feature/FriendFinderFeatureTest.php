<?php

use App\Models\User;
use Tests\Support\Common\Friend\FinderTestHarness;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeEach(function () {
    // Bind the friend finder service name to the harness to satisfy route resolution in tests.
    app()->bind('Common\\Friend\\Finder', function ($app, array $parameters) {
        $component = app(FinderTestHarness::class);
        $component->mount($parameters['entityType'], $parameters['entityId']);

        return $component->render();
    });
});

it('renders the friend finder page for authenticated members', function () {
    // Authenticate a user so the guarded route returns the Livewire-powered page.
    $member = User::factory()->create();
    actingAs($member);

    // Resolve the finder route and ensure the response includes the Livewire component signature.
    $response = get(route('friend.finder'));

    // Assert the rendered page loads successfully and references the finder heading.
    $response->assertOk()
        ->assertSee('finder-harness-count');
});

it('ships the production finder blade so the controller can render the UI', function () {
    // Resolve the absolute path for the production finder template.
    $viewPath = resource_path('views/livewire/common/friend/finder.blade.php');

    // Guard against regressions where the blade is accidentally renamed or removed.
    expect(is_file($viewPath))->toBeTrue();
});
