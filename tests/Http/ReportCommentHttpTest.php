<?php

use App\Http\Livewire\Content\ReportComment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

/**
 * HTTP-level integration tests for mounting the Livewire component via routing.
 */
beforeEach(function () {
    // Recreate the lightweight schema prior to each HTTP assertion so the
    // on-the-fly route has the necessary tables to query.
    prepareTestDatabase();
});

it('renders the report form over HTTP when the viewer has not reported the comment yet', function () {
    // Establish the comment author and the user performing the moderation action.
    $author = User::factory()->create();
    $reporter = User::factory()->create();

    // Prepare the underlying post and comment records referenced by the route.
    $postId = Post::query()->create([
        'user_id' => $author->id,
        'content' => 'Post intended to host a contentious reply.',
    ])->id;

    $commentId = DB::table('comments')->insertGetId([
        'user_id' => $author->id,
        'post_id' => $postId,
        'content' => 'Reply content under investigation.',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // Expose a temporary route that maps directly to the Livewire component for verification.
    Route::middleware('web')->get('/testing/report-comment/{commentId}', function (int $commentId) {
        // Resolve and mount the component manually to avoid pulling in the global layout shell during the test.
        $component = app(ReportComment::class);
        $component->mount($commentId);

        return view('livewire.report-comment', [
            'reported' => $component->reported,
            'reason' => $component->reason,
        ]);
    });

    // Authenticate the viewer and request the route to confirm the Livewire payload renders correctly.
    $this->actingAs($reporter);
    $response = $this->get("/testing/report-comment/{$commentId}");

    // Ensure the Livewire response surfaces the submission button so the user can file the report.
    $response->assertOk();
    $response->assertSee('Report');
    // Check that the prompt rendered from the Blade view is visible, reaffirming
    // that the Livewire component remains connected to the template markup.
    $response->assertSee('Why are you reporting this?');
});
