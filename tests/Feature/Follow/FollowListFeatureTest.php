<?php

use App\Models\User;
use Illuminate\Support\Facades\File;

use function Pest\Laravel\actingAs;

it('renders the followers route with filtered results', function (): void {
    // Ensure the in-memory SQLite schema mirrors the production tables before seeding data.
    prepareTestDatabase();

    // Seed a viewer and multiple potential followers so the component has searchable data to work with.
    $viewer = User::factory()->create();
    User::factory()->create(['name' => 'Taylor Tailwag']);
    User::factory()->create(['name' => 'Milo Purr']);

    actingAs($viewer);

    // Provide a minimal Vite manifest so the layout can resolve asset URLs during the request cycle.
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

        // Hitting the followers route should surface only records matching the search term.
        $response = $this->get(route('followers', ['search' => 'Taylor']));

        $response->assertOk();
        $response->assertSee('Followers');
        $response->assertSee('No trending tags yet.');
        // Confirm that only the searched-for follower appears in the HTML payload.
        $response->assertSee('Taylor Tailwag');
        $response->assertDontSee('Milo Purr');
    } finally {
        // Clean up the manifest stub so future tests start from a blank slate.
        File::delete(public_path('build/manifest.json'));
        File::deleteDirectory(public_path('build'));
    }
});
