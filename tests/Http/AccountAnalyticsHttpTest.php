<?php

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Vite;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

/**
 * HTTP level tests validate the route middleware and permission gates.
 */
describe('Account analytics HTTP access', function () {
    it('allows authenticated members with analytics permission to reach the dashboard route', function () {
        // Retain the original RBAC definition for cleanup at the end of the scenario.
        $originalRoles = config('access.roles');
        config(['access.roles.user.permissions' => array_merge(
            $originalRoles['user']['permissions'] ?? [],
            ['analytics.view_self']
        )]);

        $member = User::factory()->create([
            'role' => 'user',
        ]);

        actingAs($member);

        // Provision lightweight tag tables so dashboard widgets requiring trending tags avoid runtime errors.
        if (! Schema::hasTable('tags')) {
            Schema::create('tags', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('post_tag')) {
            Schema::create('post_tag', function (Blueprint $table) {
                $table->id();
                $table->foreignId('post_id');
                $table->foreignId('tag_id');
            });
        }

        // Prime a lightweight Vite manifest so Livewire can render the dashboard without hitting the build pipeline.
        File::ensureDirectoryExists(public_path('build'));
        $manifestPath = public_path('build/manifest.testing.json');
        File::put($manifestPath, json_encode([
            'resources/js/app.js' => [
                'file' => 'assets/app.js',
                'isEntry' => true,
                'src' => 'resources/js/app.js',
                'css' => ['assets/app.css'],
            ],
            'resources/css/app.css' => [
                'file' => 'assets/app.css',
                'isEntry' => true,
                'src' => 'resources/css/app.css',
            ],
        ], JSON_PRETTY_PRINT));

        Vite::useManifestFilename('manifest.testing.json');

        try {
            // The Livewire-powered route should respond successfully when the gate passes.
            $response = get('/account/analytics');
            $response->assertOk();

            // Confirm the rendered page includes copy sourced from the analytics blade template.
            $response->assertSee(e(__('common.analytics_filters_title')));
        } finally {
            // Restore the original manifest expectations and clean up the temporary artifact.
            Vite::useManifestFilename('manifest.json');
            File::delete($manifestPath);
            config(['access.roles' => $originalRoles]);
        }
    });

    it('blocks authenticated members when analytics permissions are absent', function () {
        $originalRoles = config('access.roles');

        // Strip analytics permissions from the member role to trigger the gate denial path.
        config(['access.roles.user.permissions' => ['profile.update', 'privacy.update']]);

        $member = User::factory()->create([
            'role' => 'user',
        ]);

        actingAs($member);

        // The gate should forbid access, yielding a 403 response for the dashboard route.
        get('/account/analytics')->assertForbidden();

        config(['access.roles' => $originalRoles]);
    });
});
