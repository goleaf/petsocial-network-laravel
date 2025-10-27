<?php

use App\Http\Livewire\EditProfile;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

/**
 * Feature tests verifying the Livewire-powered profile editor updates models and media correctly.
 */
it('updates profile metadata and replaces media uploads', function () {
    // Ensure the in-memory sqlite schema is rebuilt before seeding factories for this scenario.
    prepareTestDatabase();

    // Fake the public disk so file storage assertions remain isolated from the real filesystem.
    Storage::fake('public');

    // Seed a user and associated profile with existing media that should be replaced during the update.
    $user = User::factory()->create();
    $profile = Profile::create([
        'user_id' => $user->id,
        'bio' => 'Original biography',
        'avatar' => 'avatars/original-avatar.jpg',
        'cover_photo' => 'covers/original-cover.jpg',
        'location' => 'Old Location',
    ]);

    // Store placeholder files to verify that the component deletes them once replacements are uploaded.
    Storage::disk('public')->put('avatars/original-avatar.jpg', 'avatar');
    Storage::disk('public')->put('covers/original-cover.jpg', 'cover');

    // Authenticate as the seeded user so the Livewire component can resolve the correct profile relationship.
    actingAs($user);

    // Start the session so flash messages emitted by the component are captured for assertions.
    Session::start();

    // Exercise the Livewire component by uploading replacement media and updating textual fields.
    $component = Livewire::test(EditProfile::class)
        ->set('bio', 'Updated biography for the feature test')
        ->set('location', 'Austin, TX')
        ->set('newAvatar', UploadedFile::fake()->image('new-avatar.jpg'))
        ->set('newCoverPhoto', UploadedFile::fake()->image('new-cover.jpg', 1200, 400));

    $component->call('updateProfile')
        ->assertSet('bio', 'Updated biography for the feature test')
        ->assertSet('location', 'Austin, TX');

    // Reload the profile to confirm the persisted attributes mirror the Livewire component state.
    $profile->refresh();
    expect($profile->bio)->toBe('Updated biography for the feature test');
    expect($profile->location)->toBe('Austin, TX');

    // Ensure new media was stored while the originals were removed as part of the cleanup routine.
    Storage::disk('public')->assertMissing('avatars/original-avatar.jpg');
    Storage::disk('public')->assertMissing('covers/original-cover.jpg');
    Storage::disk('public')->assertExists($profile->avatar);
    Storage::disk('public')->assertExists($profile->cover_photo);
});
