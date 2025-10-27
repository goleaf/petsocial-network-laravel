<?php

use App\Http\Livewire\EditProfile;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Support\Facades\File;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

/**
 * HTTP tests ensuring the profile edit route wires the Livewire component correctly.
 */
it('renders the edit profile component on the profile edit page', function () {
    // Seed a minimal Vite manifest so the Blade layout can resolve asset references during the request.
    File::ensureDirectoryExists(public_path('build'));
    File::put(public_path('build/manifest.json'), json_encode([
        'resources/css/app.css' => ['file' => 'app.css', 'src' => 'resources/css/app.css'],
        'resources/js/app.js' => ['file' => 'app.js', 'isEntry' => true, 'src' => 'resources/js/app.js'],
    ]));

    // Create a user and profile so the page has data to hydrate while the request executes.
    $user = User::factory()->create();
    Profile::create([
        'user_id' => $user->id,
        'bio' => 'Visible via HTTP',
        'avatar' => null,
        'cover_photo' => null,
        'location' => 'Denver, CO',
    ]);

    // Ensure the Livewire alias exists in the component registry for Blade rendering.
    Livewire::component('edit-profile', EditProfile::class);
    File::put(base_path('bootstrap/cache/livewire-components.php'), '<?php return ' . var_export([
        'edit-profile' => EditProfile::class,
    ], true) . ';');

    // Authenticate and request the edit profile screen where the Livewire component is embedded.
    actingAs($user);
    $response = $this->get(route('profile.edit'));

    // Validate that the page loaded successfully and returned the profile edit view embedding the Livewire widget.
    $response->assertOk();
    $response->assertViewIs('profile.edit');

    // Clean up generated assets to avoid leaking state into subsequent HTTP scenarios.
    File::delete(public_path('build/manifest.json'));
    File::delete(base_path('bootstrap/cache/livewire-components.php'));
});
