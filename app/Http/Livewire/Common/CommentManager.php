<?php

namespace App\Http\Livewire\Common;

use App\Models\ActivityLog;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use App\Notifications\ActivityNotification;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;
use Livewire\WithPagination;

class CommentManager extends Component
{
    use WithPagination;

    public $postId;

    public $post;

    public $content;

    public $editingCommentId;

    public $editingContent;

    public $replyingToId;

    public $showAllComments = false;

    public $commentsPerPage = 5;

    protected $paginationTheme = 'tailwind';

    protected $listeners = [
        'refreshComments' => '$refresh',
        'commentAdded' => '$refresh',
        'commentUpdated' => '$refresh',
        'commentDeleted' => '$refresh',
    ];

    protected $rules = [
        'content' => 'required|max:500',
    ];

    public function mount($postId)
    {
        $this->postId = $postId;
        $this->post = Post::findOrFail($postId);
    }

    public function toggleShowAllComments()
    {
        $this->showAllComments = ! $this->showAllComments;
        $this->resetPage();
    }

    public function save()
    {
        $this->validate();

        $data = [
            'user_id' => auth()->id(),
            'post_id' => $this->postId,
            'content' => $this->content,
        ];

        if ($this->replyingToId) {
            $data['parent_id'] = $this->replyingToId;
        }

        $comment = Comment::create($data);

        // Send notification to post owner
        if ($this->post->user_id !== auth()->id()) {
            $this->post->user->notify(new ActivityNotification('comment', auth()->user(), $this->post));
        }

        // Send notifications to mentioned users
        $mentionedUsers = $this->parseMentions($this->content);
        foreach ($mentionedUsers as $user) {
            if ($user->id !== auth()->id()) {
                $user->notify(new ActivityNotification('mention', auth()->user(), $this->post));
            }
        }

        // Log activity
        ActivityLog::record(
            auth()->user(),
            'comment_added',
            "Commented on post ID {$this->postId}: ".substr($this->content, 0, 50).(strlen($this->content) > 50 ? '...' : ''),
            [
                'post_id' => $this->postId,
                'comment_id' => $comment->id,
                'preview' => substr($this->content, 0, 120),
            ]
        );

        // Clear cache
        $this->clearCommentsCache();

        // Reset form
        $this->reset(['content', 'replyingToId']);

        // Emit event for other components
        $this->emit('commentAdded');
    }

    public function reply($commentId)
    {
        $this->replyingToId = $commentId;
    }

    public function cancelReply()
    {
        $this->replyingToId = null;
    }

    public function edit($commentId)
    {
        $comment = Comment::where('user_id', auth()->id())->find($commentId);

        if ($comment) {
            $this->editingCommentId = $commentId;
            $this->editingContent = $comment->content;
        }
    }

    public function update()
    {
        $this->validate([
            'editingContent' => 'required|max:500',
        ]);

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

            // Clear cache
            $this->clearCommentsCache();

            // Reset form
            $this->reset(['editingCommentId', 'editingContent']);

            // Emit event for other components
            $this->emit('commentUpdated');
        }
    }

    public function delete($commentId)
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

            // Clear cache
            $this->clearCommentsCache();

            // Emit event for other components
            $this->emit('commentDeleted');
        }
    }

    protected function clearCommentsCache()
    {
        // Clear cache for comments
        Cache::forget("post_{$this->postId}_comments");
        Cache::forget("post_{$this->postId}_comments_count");
        Cache::forget("post_{$this->postId}_top_comments");
    }

    protected function parseMentions($content)
    {
        preg_match_all('/@(\w+)/', $content, $matches);

        return User::whereIn('name', $matches[1])->get();
    }

    protected function getComments()
    {
        if (! $this->showAllComments) {
            // Get only top-level comments with limited replies
            $cacheKey = "post_{$this->postId}_top_comments";

            return Cache::remember($cacheKey, now()->addMinutes(5), function () {
                return Comment::where('post_id', $this->postId)
                    ->whereNull('parent_id')
                    ->with(['user', 'replies' => function ($query) {
                        $query->with('user')->latest()->limit(2);
                    }])
                    ->latest()
                    ->limit($this->commentsPerPage)
                    ->get();
            });
        }

        // Get all comments with pagination
        $cacheKey = "post_{$this->postId}_comments_page_{$this->page}";

        return Cache::remember($cacheKey, now()->addMinutes(5), function () {
            return Comment::where('post_id', $this->postId)
                ->whereNull('parent_id')
                ->with(['user', 'replies' => function ($query) {
                    $query->with('user');
                }])
                ->latest()
                ->paginate($this->commentsPerPage);
        });
    }

    protected function getCommentsCount()
    {
        $cacheKey = "post_{$this->postId}_comments_count";

        return Cache::remember($cacheKey, now()->addMinutes(5), function () {
            return Comment::where('post_id', $this->postId)->count();
        });
    }

    public function render()
    {
        return view('livewire.common.comment-manager', [
            'comments' => $this->getComments(),
            'commentsCount' => $this->getCommentsCount(),
        ]);
    }
}
