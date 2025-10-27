<?php

use App\Http\Livewire\UserDashboard;
use App\Models\Profile;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

/**
 * HTTP coverage validating the dashboard component renders through a web route.
 */
it('serves the dashboard component over an HTTP route', function () {
    // Freeze time so the seeded post appears consistently in assertions.
    Carbon::setTestNow(Carbon::parse('2025-05-03 08:30:00'));

    // Provision an authenticated member with a profile to satisfy avatar lookups in the view.
    $member = User::factory()->create(['name' => 'Jordan']);
    Profile::create(['user_id' => $member->id]);
    actingAs($member);

    // Seed a feed entry to ensure the rendered response contains dynamic dashboard content.
    DB::table('posts')->insert([
        'user_id' => $member->id,
        'content' => 'Timeline preview message',
        'posts_visibility' => 'public',
        'created_at' => now()->subMinutes(2),
        'updated_at' => now()->subMinutes(2),
    ]);

    // Register a temporary route that mounts the Livewire dashboard component through the web middleware stack.
    Route::middleware('web')->get('/test-dashboard', UserDashboard::class);

    // Issue a request and confirm the component responds successfully with the expected copy.
    get('/test-dashboard')
        ->assertOk()
        ->assertSee('Welcome, '.$member->name.'!')
        ->assertSee('Timeline preview message');

    // Release the frozen clock to avoid affecting unrelated suites.
    Carbon::setTestNow();
});
