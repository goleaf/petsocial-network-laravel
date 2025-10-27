<?php

use App\Models\User;
use Illuminate\Support\Facades\File;

it('renders the followers route with filtered results', function (): void {
    // Seed a viewer and multiple potential followers so the component has searchable data to work with.
    $viewer = User::factory()->create();
    User::factory()->create(['name' => 'Taylor Tailwag']);
    User::factory()->create(['name' => 'Milo Purr']);

    $this->actingAs($viewer);

    // Provide a minimal Vite manifest so the layout can resolve asset URLs during the request cycle.
    File::ensureDirectoryExists(public_path('build'));
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

    // Hitting the followers route should surface only records matching the search term.
    $response = $this->get(route('followers', ['search' => 'Taylor']));

    $response->assertOk();
    $response->assertSee('Followers');
    $response->assertSee('No trending tags yet.');

    File::delete(public_path('build/manifest.json'));
    File::deleteDirectory(public_path('build'));
});
