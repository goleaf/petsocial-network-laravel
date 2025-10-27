<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('redirects guests away from the notification center route', function (): void {
    // Guests should be prompted to authenticate before reviewing notifications.
    $response = $this->get('/notifications');

    // Verify the standard authentication redirect fires for unauthenticated visitors.
    $response->assertRedirect(route('login'));
});

it('allows authenticated members to load the notification center endpoint', function (): void {
    // Seed a member profile that will request its notification feed via HTTP.
    $member = User::factory()->create([
        'privacy_settings' => User::PRIVACY_DEFAULTS,
    ]);

    // Authenticate the member to satisfy the route middleware stack.
    $this->actingAs($member);

    // Visiting the notifications URL should render the Livewire-powered Blade view.
    $response = $this->get('/notifications');

    $response->assertOk();
    $response->assertSeeLivewire('common.notification-center');
});
