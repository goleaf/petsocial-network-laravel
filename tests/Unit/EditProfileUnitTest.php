<?php

use App\Http\Livewire\EditProfile;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;

/**
 * Unit tests confirming the component updates textual fields without requiring file uploads.
 */
it('persists textual profile updates when no new media is supplied', function () {
    // Rebuild the sqlite schema to provide the users and profiles tables for this isolated unit scenario.
    prepareTestDatabase();

    // Fake the disk to prevent accidental filesystem writes during the isolated unit test.
    Storage::fake('public');

    // Provision a user profile record with existing media references that should remain untouched.
    $user = User::factory()->create();
    Profile::create([
        'user_id' => $user->id,
        'bio' => 'Original bio text',
        'avatar' => 'avatars/persist-avatar.jpg',
        'cover_photo' => 'covers/persist-cover.jpg',
        'location' => 'Initial City',
    ]);

    // Authenticate to satisfy the component's reliance on the currently signed-in user.
    actingAs($user);

    // Partially mock the component so we can stub the validation response while exercising the update logic directly.
    $component = \Mockery::mock(EditProfile::class)->makePartial();
    $component->bio = 'Original bio text';
    $component->location = 'Initial City';
    $component->avatar = 'avatars/persist-avatar.jpg';
    $component->coverPhoto = 'covers/persist-cover.jpg';
    $component->newAvatar = null;
    $component->newCoverPhoto = null;

    // Provide sanitized validation data that only mutates textual attributes and leaves media null.
    $component->shouldReceive('validate')->once()->andReturn([
        'bio' => 'Refined biography line',
        'location' => 'Portland, OR',
        'newAvatar' => null,
        'newCoverPhoto' => null,
    ]);

    // Run the update routine which should persist the textual changes and keep the original media pointers.
    $component->updateProfile();

    // Verify the database reflects the updated text fields while the media references remain unchanged.
    $profile = $user->profile()->first();
    expect($profile->bio)->toBe('Refined biography line');
    expect($profile->location)->toBe('Portland, OR');
    expect($profile->avatar)->toBe('avatars/persist-avatar.jpg');
    expect($profile->cover_photo)->toBe('covers/persist-cover.jpg');

    // Close the mockery container to mirror other unit tests and avoid memory leaks across the suite.
    \Mockery::close();
});

/**
 * Unit tests can also verify that the Livewire component resolves the expected Blade template.
 */
it('renders the edit profile blade view for layout composition', function () {
    // Instantiate the component directly to focus purely on the render contract without Livewire bootstrapping.
    $component = new EditProfile();

    // Invoke the render method and confirm the returned view instance references the expected Blade file.
    $view = $component->render();
    expect($view)->toBeInstanceOf(\Illuminate\View\View::class);
    expect($view->name())->toBe('livewire.edit-profile');
});
