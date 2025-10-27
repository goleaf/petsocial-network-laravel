<?php

use App\Models\User;
use Illuminate\Support\Facades\File;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

it('redirects guests away from the followers listing', function (): void {
    // Attempting to open the followers page as a guest should bounce to the login form.
    $response = get(route('followers'));

    $response->assertRedirect(route('login'));
});

it('renders Livewire bindings for the followers list over HTTP', function (): void {
    // Set up the in-memory database schema so user factories can persist records.
    prepareTestDatabase();

    // Create a viewer so we can authenticate against the protected followers route.
    $viewer = User::factory()->create();
    actingAs($viewer);

    // Seed a manifest stub that mimics Vite output to satisfy the layout asset loader.
    File::ensureDirectoryExists(public_path('build'));

    try {
        File::put(public_path('build/manifest.json'), json_encode([
            'resources/css/app.css' => [
                'file' => 'assets/app.css',
                'src' => 'resources/css/app.css',
                'isEntry' => true,
            ],
            'resources/js/app.js' => [
                'file' => 'assets/app.js',
                'src' => 'resources/js/app.js',
                'isEntry' => true,
            ],
        ]));

        // Load the followers route and confirm the rendered HTML carries the Livewire bindings.
        $response = get(route('followers'));

        $response->assertOk();
        $response->assertSee('Followers');
        $response->assertSee('wire:model.debounce.400ms="search"', false);
        $response->assertSee('wire:model="perPage"', false);
    } finally {
        // Tear down the manifest directory so later tests do not inherit the stub files.
        File::delete(public_path('build/manifest.json'));
        File::deleteDirectory(public_path('build'));
    }
});
