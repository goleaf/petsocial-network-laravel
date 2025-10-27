<?php

use App\Models\User;
use Tests\Support\Common\Friend\FinderTestHarness;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

it('redirects guests away from the protected friend finder endpoint', function () {
    // Issue the request without authenticating to verify middleware enforcement.
    $response = get(route('friend.finder'));

    // Guests should be redirected to the login screen when accessing the finder.
    $response->assertRedirect(route('login'));
});

it('returns a successful response for authenticated requests', function () {
    // Log in as a member to satisfy the route guard for the finder page.
    $member = User::factory()->create();
    actingAs($member);

    // Bind the container alias so the route resolves the finder harness while testing.
    app()->bind('Common\\Friend\\Finder', function ($app, array $parameters) {
        $component = app(FinderTestHarness::class);
        $component->mount($parameters['entityType'], $parameters['entityId']);

        return $component->render();
    });

    // Perform the HTTP request and ensure the component renders successfully.
    $response = get(route('friend.finder'));

    // Confirm the HTTP layer reports success for authorised access.
    $response->assertOk();
});
