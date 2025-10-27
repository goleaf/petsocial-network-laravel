<?php

use App\Http\Livewire\Account\Analytics;
use App\Models\User;
use Symfony\Component\HttpKernel\Exception\HttpException;

use function Pest\Laravel\actingAs;

/**
 * Account analytics access scenarios driven by RBAC permissions.
 */
describe('Account analytics access control', function () {
    it('allows members with self analytics permission to load the dashboard', function () {
        // Preserve the original configuration so it can be restored after the test run.
        $originalRoles = config('access.roles');

        // Ensure the standard user role includes self analytics access during the scenario.
        config(['access.roles.user.permissions' => array_merge(
            $originalRoles['user']['permissions'] ?? [],
            ['analytics.view_self']
        )]);

        // Create a member and authenticate them to exercise the Livewire component.
        $member = User::factory()->create([
            'role' => 'user',
        ]);
        actingAs($member);

        $component = \Mockery::mock(Analytics::class)->makePartial();
        $component->shouldAllowMockingProtectedMethods();
        $component->shouldReceive('loadAnalytics')->once()->with($member);

        $component->mount();
        expect($component->overview)->toBeArray();

        // Restore the access configuration for isolation across subsequent tests.
        config(['access.roles' => $originalRoles]);
        \Mockery::close();
    });

    it('blocks members lacking analytics permissions from mounting the dashboard', function () {
        // Capture the default role definitions to reset once the scenario finishes.
        $originalRoles = config('access.roles');

        // Strip analytics permissions so the guard clause triggers.
        config(['access.roles.user.permissions' => ['profile.update', 'privacy.update']]);

        $member = User::factory()->create([
            'role' => 'user',
        ]);
        actingAs($member);

        $component = \Mockery::mock(Analytics::class)->makePartial();
        $component->shouldAllowMockingProtectedMethods();
        $component->shouldNotReceive('loadAnalytics');

        expect(fn () => $component->mount())->toThrow(HttpException::class);

        // Reinstate the original configuration after the assertion.
        config(['access.roles' => $originalRoles]);
        \Mockery::close();
    });
});
