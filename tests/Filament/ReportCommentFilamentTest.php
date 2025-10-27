<?php

use App\Http\Livewire\Content\ReportComment;
use App\Models\CommentReport;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Filament compatibility checks for embedding the Livewire component in admin tooling.
 */
it('can be resolved through a simulated Filament action wrapper', function () {
    // Create the comment author and moderation reviewer to drive the simulated Filament action.
    $author = User::factory()->create();
    $moderator = User::factory()->create();

    // Persist the post and comment that the component will reference during mounting.
    $postId = Post::query()->create([
        'user_id' => $author->id,
        'content' => 'Content needing moderator attention.',
    ])->id;

    $commentId = DB::table('comments')->insertGetId([
        'user_id' => $author->id,
        'post_id' => $postId,
        'content' => 'Comment escalated to the moderation queue.',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // Seed an existing report so the component should recognise the prior action when mounted.
    CommentReport::query()->create([
        'user_id' => $moderator->id,
        'comment_id' => $commentId,
        'reason' => 'Repeated policy violations detected.',
    ]);

    // Mimic Filament resolving the Livewire component inside an action or widget context.
    $filamentAction = new class {
        public function resolveComponent(int $commentId): ReportComment
        {
            // Resolve the component through the service container just like Filament would.
            $component = app(ReportComment::class);
            $component->mount($commentId);

            return $component;
        }
    };

    // Authenticate the moderator so the component sees the correct viewer identity.
    $this->actingAs($moderator);
    $component = $filamentAction->resolveComponent($commentId);

    // Validate that the embedded component reflects the pre-existing report state for the moderator.
    expect($component->reported)->toBeTrue();
});
