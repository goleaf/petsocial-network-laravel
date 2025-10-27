<?php

use App\Http\Livewire\Content\ReportComment;
use App\Models\CommentReport;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Unit exercises for the comment reporting component internals.
 */
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
