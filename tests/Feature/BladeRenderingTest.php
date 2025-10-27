<?php

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ViewErrorBag;

beforeEach(function (): void {
    /**
     * Guarantees icons relying on dash attributes receive default stroke widths during tests.
     */
    View::composer('components.icons.*', function ($view): void {
        if (! array_key_exists('strokeWidth', $view->getData())) {
            $view->with('strokeWidth', '1.5');
        }
    });

    /**
     * Synthesizes a lightweight Vite manifest so marketing blades can compile.
     */
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

    /**
     * Provides a temporary environment file so Blade helpers relying on env() stay calm.
     */
    if (! File::exists(base_path('.env'))) {
        File::put(base_path('.env'), "APP_KEY=base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=\nAPP_NAME=PestTest\n");
    }
});

afterEach(function (): void {
    /**
     * Removes the synthetic manifest to keep the test workspace tidy.
     */
    File::delete(public_path('build/manifest.json'));
    File::deleteDirectory(public_path('build'));

    /**
     * Leaves the seeded environment file in place for subsequent tests.
     */
});

/**
 * Validates that critical guest-facing Blade views render successfully.
 */
it('renders guest surfaces without missing components', function (): void {
    $errorBag = new ViewErrorBag;

    expect(File::exists(base_path('.env')))->toBeTrue();
    expect(View::make('welcome')->render())->toBeString()->not->toBeEmpty();
    expect(View::make('layouts.guest', ['slot' => ''])->render())->toBeString()->not->toBeEmpty();
    expect(View::make('auth.login', ['errors' => $errorBag])->render())->toBeString()->not->toBeEmpty();
    expect(View::make('auth.register', ['errors' => $errorBag])->render())->toBeString()->not->toBeEmpty();
});
