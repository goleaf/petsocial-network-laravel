<?php

use App\Http\Livewire\Common\Friend\Analytics as FriendAnalyticsComponent;
use App\Models\User;

use Illuminate\Database\Eloquent\ModelNotFoundException;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    // Mirror the application container binding that resolves the analytics
    // component for the route shortcuts defined in the friends namespace.
    app()->bind('Common\\Friend\\Analytics', function ($app, array $parameters = []) {
        return $app->make(FriendAnalyticsComponent::class, $parameters);
    });
});

/**
 * Low-level HTTP coverage for the friend analytics routes to ensure middleware
 * and error handling behave as expected.
 */
it('redirects guests away from the friend analytics dashboard', function () {
    // Attempt to load the member analytics dashboard without authentication and
    // confirm the guest is redirected to the login route.
    $this->get(route('friend.analytics'))->assertRedirect(route('login'));
});

it('returns not found when requesting analytics for an unknown pet', function () {
    // Authenticate as a user to mirror the guard configuration and assert that
    // attempting to mount the pet analytics component with an invalid ID fails.
    $member = User::factory()->create();
    actingAs($member);

    expect(fn () => app('Common\\Friend\\Analytics', [
        'entityType' => 'pet',
        'entityId' => 999,
    ])->mount('pet', 999))->toThrow(ModelNotFoundException::class);
});
