<?php

use App\Models\Pet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('redirects guests away from the posts manager route', function (): void {
    // Guests should be prompted to authenticate before accessing the composer feed.
    $response = $this->get('/posts');

    $response->assertRedirect(route('login'));
});

it('allows authenticated members to load the posts manager endpoint', function (): void {
    // Authenticate a user whose feed will be resolved through the HTTP route.
    $member = User::factory()->create([
        'profile_visibility' => 'public',
        'privacy_settings' => User::PRIVACY_DEFAULTS,
    ]);

    $this->actingAs($member);

    // Visiting the posts route should return the Livewire-powered view for the component.
    $response = $this->get('/posts');

    $response->assertOk();
    $response->assertSeeLivewire('common.post-manager');
});

it('blocks non owners from accessing private pet post feeds', function (): void {
    // Create a pet with private visibility and a different authenticated viewer.
    $owner = User::factory()->create([
        'profile_visibility' => 'public',
        'privacy_settings' => User::PRIVACY_DEFAULTS,
    ]);

    $pet = Pet::factory()->create([
        'user_id' => $owner->id,
        'visibility' => 'private',
    ]);

    $viewer = User::factory()->create([
        'profile_visibility' => 'public',
        'privacy_settings' => User::PRIVACY_DEFAULTS,
    ]);

    $this->actingAs($viewer);

    // Attempting to load the private pet feed should trigger a forbidden response via the component mount guard.
    $response = $this->get(route('pet.posts', ['petId' => $pet->id]));

    $response->assertForbidden();
});
