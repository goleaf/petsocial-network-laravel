<?php

use App\Http\Livewire\Content\ReportComment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

/**
 * Livewire-centric tests for the comment reporting workflow.
 */
beforeEach(function () {
    // Refresh the transient SQLite database before each Livewire scenario to
    // guarantee the schema aligns with the component's expectations.
    prepareTestDatabase();
});

describe('ReportComment Livewire validation', function () {
    it('requires a reason before submitting a report', function () {
        // Provision the actors required for the component to bootstrap correctly.
        $author = User::factory()->create();
        $reporter = User::factory()->create();

        // Record a post and comment for the Livewire component to reference.
        $postId = Post::query()->create([
            'user_id' => $author->id,
            'content' => 'Another post that accumulates replies.',
        ])->id;

        $commentId = DB::table('comments')->insertGetId([
            'user_id' => $author->id,
            'post_id' => $postId,
            'content' => 'Short reply awaiting moderation scrutiny.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Authenticate as the reporting user so the component leverages the guard context.
        $this->actingAs($reporter);

        // Attempt to submit the form without a reason and verify validation feedback triggers.
        Livewire::test(ReportComment::class, ['commentId' => $commentId])
            ->call('report')
            ->assertHasErrors(['reason' => 'required'])
            // Ensure the Livewire component is wired to the dedicated Blade view so
            // design updates stay centralised in `resources/views/livewire`.
            ->assertViewIs('livewire.report-comment');
    });
});
