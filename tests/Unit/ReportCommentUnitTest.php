<?php

use App\Http\Livewire\Content\ReportComment;
use App\Models\CommentReport;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;

/**
 * Unit exercises for the comment reporting component internals.
 */
beforeEach(function () {
    // Reset the SQLite schema before each unit assertion so manual model
    // creation has consistent tables and constraints to target.
    prepareTestDatabase();
});

it('marks the component as reported when the viewer already submitted feedback', function () {
    // Create the original author and the viewer who will test the repeat-report safeguard.
    $author = User::factory()->create();
    $viewer = User::factory()->create();

    // Insert a post and related comment so the component has a concrete target to evaluate.
    $postId = Post::query()->create([
        'user_id' => $author->id,
        'content' => 'Post awaiting moderation review.',
    ])->id;

    $commentId = DB::table('comments')->insertGetId([
        'user_id' => $author->id,
        'post_id' => $postId,
        'content' => 'Comment already reported previously.',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // Persist the initial report to emulate a prior action logged by the viewer.
    CommentReport::query()->create([
        'user_id' => $viewer->id,
        'comment_id' => $commentId,
        'reason' => 'Test reason demonstrating duplicate handling.',
    ]);

    // Authenticate as the viewer and mount the component to confirm it registers the existing report.
    $this->actingAs($viewer);
    $component = new ReportComment();
    $component->mount($commentId);

    // Verify the reported flag is raised so the Livewire view disables additional submissions.
    expect($component->reported)->toBeTrue();
});

it('renders the expected Blade view when invoked directly', function () {
    // Leverage the Blade facade to confirm the template exists so refactors do
    // not accidentally remove the Livewire partial from the view directory.
    expect(View::exists('livewire.report-comment'))->toBeTrue();

    // Instantiate the component and call render() to capture the resolved view
    // instance, ensuring the Livewire output references the same template.
    $component = new ReportComment();
    $renderedView = $component->render();

    // Validate the rendered view uses the correct name so downstream tests can
    // rely on the established Blade include.
    expect($renderedView->name())->toBe('livewire.report-comment');
});
