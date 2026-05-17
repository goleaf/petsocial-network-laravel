<?php

namespace App\Http\Livewire\Content;

use App\Models\ActivityLog;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use App\Notifications\ActivityNotification;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Component;
use Livewire\WithPagination;

class CommentSection extends Component
{
    use WithPagination;

    public $postId;

    public $content;

    public $editingCommentId;

    public $editingContent;

    public $replyingToId;

    public LengthAwarePaginator $comments;

    public function mount(int $postId): void
    {
        $this->postId = $postId;
        $this->loadComments();
    }

    public function loadComments(): void
    {
        // Reset pagination so new interactions always reveal the freshest conversation state.
        $this->resetPage();
    }

    public function save(): void
    {
        $this->validate(['content' => 'required|max:255']);
        $data = [
            'user_id' => auth()->id(),
            'post_id' => $this->postId,
            'content' => $this->content,
        ];
        if ($this->replyingToId) {
            $data['parent_id'] = $this->replyingToId;
        }
        $comment = Comment::create($data);
        $post = Post::find($this->postId);
        if ($post->user_id !== auth()->id()) {
            $post->user->notify(new ActivityNotification('comment', auth()->user(), $post));
        }
        $mentionedUsers = $this->parseMentions($this->content);
        foreach ($mentionedUsers as $user) {
            if ($user->id !== auth()->id()) {
                $user->notify(new ActivityNotification('mention', auth()->user(), $post));
            }
        }
        ActivityLog::record(
            auth()->user(),
            'comment_added',
            "Commented on post ID {$this->postId}: {$this->content}",
            [
                'post_id' => $this->postId,
                'comment_id' => $comment->id,
                'preview' => substr($this->content, 0, 120),
            ]
        );
        $this->content = '';
        $this->replyingToId = null;
        $this->loadComments();
    }

    public function reply(int $commentId): void
    {
        $this->replyingToId = $commentId;
    }

    public function edit(int $commentId): void
    {
        $comment = Comment::where('user_id', auth()->id())->find($commentId);
        if ($comment) {
            $this->editingCommentId = $commentId;
            $this->editingContent = $comment->content;
        }
    }

    public function update(): void
    {
        $this->validate(['editingContent' => 'required|max:255']);
        $comment = Comment::where('user_id', auth()->id())->find($this->editingCommentId);
        if ($comment) {
            $comment->update(['content' => $this->editingContent]);

            ActivityLog::record(
                auth()->user(),
                'comment_updated',
                "Updated comment ID {$comment->id} on post ID {$this->postId}.",
                [
                    'post_id' => $this->postId,
                    'comment_id' => $comment->id,
                    'preview' => substr($this->editingContent, 0, 120),
                ]
            );

            $this->editingCommentId = null;
            $this->editingContent = '';
            $this->loadComments();
        }
    }

    public function delete(int $commentId): void
    {
        $comment = Comment::where('user_id', auth()->id())->find($commentId);
        if ($comment) {
            $commentId = $comment->id;
            $comment->delete();

            ActivityLog::record(
                auth()->user(),
                'comment_deleted',
                "Deleted comment ID {$commentId} from post ID {$this->postId}.",
                [
                    'post_id' => $this->postId,
                    'comment_id' => $commentId,
                ]
            );

            $this->loadComments();
        }
    }

    /**
     * Resolve all mentioned users found in a comment body.
     */
    protected function parseMentions(string $content)
    {
        preg_match_all('/@(\w+)/', $content, $matches);

        return User::whereIn('name', $matches[1])->get();
    }

    /**
     * Build a mention option list compatible with Filament Commentions style inputs.
     *
     * @return array<int, array{id: int, value: string, label: string}>
     */
    public function mentionSuggestions(string $search = ''): array
    {
        $normalizedSearch = trim($search);

        return User::query()
            ->when($normalizedSearch !== '', function ($query) use ($normalizedSearch) {
                $query->where('name', 'like', "%{$normalizedSearch}%");
            })
            ->orderBy('name')
            ->limit(8)
            ->get(['id', 'name'])
            ->map(function (User $user): array {
                return [
                    'id' => $user->id,
                    'value' => '@'.$user->name,
                    'label' => $user->name,
                ];
            })
            ->all();
    }

    public function render()
    {
        $this->comments = $this->fetchComments();

        return view('livewire.comment-section', [
            // Provide the Blade template with paginated comments ready for display.
            'comments' => $this->comments,
            'mentionSuggestions' => $this->mentionSuggestions($this->extractMentionSearchTerm($this->content ?? '')),
        ]);
    }

    /**
     * Pull the in-progress @mention fragment from comment input for suggestion filtering.
     */
    protected function extractMentionSearchTerm(string $commentBody): string
    {
        if (! preg_match('/(?:^|\s)@(\w*)$/', $commentBody, $matches)) {
            return '';
        }

        return $matches[1] ?? '';
    }

    protected function fetchComments(): LengthAwarePaginator
    {
        // Gather top-level comments with eager-loaded replies for efficient nested rendering.
        return Comment::where('post_id', $this->postId)
            ->whereNull('parent_id')
            ->with(['user', 'replies' => function ($query) {
                $query->with('user');
            }])
            ->latest()
            ->paginate(5);
    }
}
