<?php

use App\Http\Livewire\Content\ReportPost;
use App\Models\Post;
use App\Models\PostReport;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\postJson;

// Exercise the component through a lightweight HTTP endpoint that proxies to the Livewire logic.
it('handles reporting via a custom http endpoint wired to the component', function (): void {
    // Build the users and post that will participate in the simulated HTTP flow.
    $author = User::factory()->create();
    $reporter = User::factory()->create();
    $post = Post::create([
        'user_id' => $author->id,
        'content' => 'Content that violates community guidelines.',
    ]);

    // Register a temporary route that leverages the component to process the payload.
    Route::middleware('web')->post('/testing/report-post', function (Request $request) {
        $component = app(ReportPost::class);
        $component->mount($request->integer('post_id'));
        $component->reason = $request->input('reason');
        $component->report();

        return response()->json([
            'reported' => $component->reported,
            'reports' => PostReport::where('post_id', $component->postId)->count(),
        ]);
    });

    actingAs($reporter);

    $response = postJson('/testing/report-post', [
        'post_id' => $post->id,
        'reason' => 'Contains misleading medical advice.',
    ]);

    // Confirm the HTTP layer reports success and the database recorded the report.
    $response->assertOk()->assertJson([
        'reported' => true,
        'reports' => 1,
    ]);
});
