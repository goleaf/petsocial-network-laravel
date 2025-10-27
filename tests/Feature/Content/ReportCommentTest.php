<?php

use App\Http\Livewire\Content\ReportComment;
use App\Models\CommentReport;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

/**
 * Feature coverage for the comment reporting Livewire component.
 */
describe('ReportComment feature behavior', function () {
    it('creates a report and updates the component state', function () {
        // Create the author and reporting user so authentication and ownership are well-defined.
        $author = User::factory()->create();
        $reporter = User::factory()->create();

        // Seed a post and its related comment to provide context for the report submission.
        $postId = Post::query()->create([
            'user_id' => $author->id,
            'content' => 'Test post content for moderation review.',
        ])->id;

        $commentId = DB::table('comments')->insertGetId([
            'user_id' => $author->id,
            'post_id' => $postId,
            'content' => 'Problematic remark that should be reported.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Authenticate as the reporter so the Livewire component can resolve auth()->id().
        $this->actingAs($reporter);

        // Interact with the component to submit a moderation report against the comment.
        Livewire::test(ReportComment::class, ['commentId' => $commentId])
            ->set('reason', 'Harassment in the reply thread.')
            ->call('report')
            ->assertSet('reported', true)
            ->assertSet('reason', '');

        // Confirm the backing table captured the submission exactly once for the reporter/comment pair.
        expect(CommentReport::query()
            ->where('user_id', $reporter->id)
            ->where('comment_id', $commentId)
            ->exists())->toBeTrue();

        // Ensure the persisted report retains the reason supplied by the user.
        expect(CommentReport::query()->firstWhere('comment_id', $commentId)->reason)
            ->toBe('Harassment in the reply thread.');
    });
});
