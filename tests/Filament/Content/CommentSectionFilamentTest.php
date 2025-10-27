<?php

use App\Http\Livewire\Content\CommentSection;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

it('presents comment data in a paginator shape that Filament table widgets can consume', function () {
    // Prepare a post and a pair of users that mirror the admin curation scenario.
    $postOwner = User::factory()->create(['name' => 'OwnerAdmin']);
    $commentAuthor = User::factory()->create(['name' => 'FilamentFan']);
    $mentioned = User::factory()->create(['name' => 'BuddyTwo']);

    $post = Post::create([
        'user_id' => $postOwner->id,
        'content' => 'Post for the dashboard',
    ]);

    // Create a comment with a mention so formattedContent can enrich the preview.
    $comment = Comment::create([
        'user_id' => $commentAuthor->id,
        'post_id' => $post->id,
        'content' => 'Moderated note for @BuddyTwo',
    ]);

    // Mount the component exactly as Filament would when embedding it inside a resource page.
    $component = app(CommentSection::class);
    $component->mount($post->id);
    $component->loadComments();

    // Ensure the component exposes a paginator instance for table builders.
    expect($component->comments)->toBeInstanceOf(LengthAwarePaginator::class);
    expect($component->comments->total())->toBe(1);

    // Simulate a lightweight Filament table data transformation using an inline helper class.
    $tableAdapter = new class ($component->comments) {
        public function __construct(private LengthAwarePaginator $records)
        {
        }

        public function rows(): array
        {
            return $this->records->getCollection()->map(function (Comment $comment) {
                return [
                    'id' => $comment->id,
                    'author' => $comment->user->name,
                    'preview' => $comment->formattedContent(),
                ];
            })->all();
        }
    };

    $rows = $tableAdapter->rows();

    // The adapter should surface one row with the formatted mention hyperlink for dashboard consumption.
    expect($rows)->toHaveCount(1);
    expect($rows[0]['id'])->toBe($comment->id);
    expect($rows[0]['author'])->toBe('FilamentFan');
    expect($rows[0]['preview'])->toContain("<a href='/profile/{$mentioned->id}'>@BuddyTwo</a>");
});
