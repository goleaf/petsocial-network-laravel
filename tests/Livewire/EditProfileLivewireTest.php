<?php

use App\Http\Livewire\EditProfile;
use App\Models\Profile;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

/**
 * Livewire tests validating component state hydration and rendering behaviour.
 */
it('hydrates state from the authenticated profile and renders the expected view', function () {
    // Prepare a user with rich profile data to confirm Livewire pre-populates every public property.
    $user = User::factory()->create();
    Profile::create([
        'user_id' => $user->id,
        'bio' => 'Curious explorer',
        'avatar' => 'avatars/existing-avatar.jpg',
        'cover_photo' => 'covers/existing-cover.jpg',
        'location' => 'Seattle, WA',
    ]);

    // Sign in as the seeded user so the component hydrates against the proper profile relationship.
    actingAs($user);

    // Mount the component and ensure Livewire exposes the stored profile attributes and renders the right Blade view.
    Livewire::test(EditProfile::class)
        ->assertSet('bio', 'Curious explorer')
        ->assertSet('avatar', 'avatars/existing-avatar.jpg')
        ->assertSet('coverPhoto', 'covers/existing-cover.jpg')
        ->assertSet('location', 'Seattle, WA')
        ->assertViewIs('livewire.edit-profile');
});
