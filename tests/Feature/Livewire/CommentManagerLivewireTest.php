<?php

use App\Http\Livewire\Common\CommentManager;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;
use function Pest\Laravel\actingAs;

it('paginates between condensed and full comment views', function (): void {
    // Relax guarding to seed the various top-level comments required for pagination checks.
    Comment::unguard();

    // Reset cached fragments so earlier tests do not influence pagination assertions.
    Cache::flush();

    // Prepare the author and post so the Livewire component can mount and resolve context.
    $author = User::factory()->create(['name' => 'author']);
    $post = Post::create([
        'user_id' => $author->id,
        'content' => 'Thread for pagination checks',
    ]);

    // Generate more comments than the per-page limit to exercise both rendering paths.
    $otherUsers = User::factory()->count(6)->create();
    foreach ($otherUsers as $index => $user) {
        // Attach a unique body to each comment so the assertions can detect distinct records.
        Comment::create([
            'user_id' => $user->id,
            'post_id' => $post->id,
            'content' => "Comment number {$index}",
        ]);
    }

    // Operate the component as the post owner to ensure cache scoping aligns with the real workflow.
    actingAs($author);

    // Drive the component and capture the initial render output for inspection.
    $component = Livewire::test(CommentManager::class, ['postId' => $post->id]);

    // Seed the paginator page state so the WithPagination trait has a baseline to work with during assertions.
    $component->instance()->page = 1;

    // Confirm the condensed view returns a collection capped at the component's page size.
    $initialComments = $component->viewData('comments');
    expect($initialComments)->toBeInstanceOf(Collection::class)
        ->and($initialComments)->toHaveCount(5);
    expect($component->viewData('commentsCount'))->toBe(6);

    // Toggle the expanded view by invading the component so we can inspect the protected pagination helper safely.
    $invader = $component->invade();
    $invader->showAllComments = true;
    $invader->page = 1;

    $expandedComments = $invader->getComments();
    expect($expandedComments)->toBeInstanceOf(LengthAwarePaginator::class)
        ->and($expandedComments->total())->toBe(6);

    // Re-enable guarding after the assertions run.
    Comment::reguard();
});
