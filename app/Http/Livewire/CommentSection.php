<?php

namespace App\Http\Livewire;

use App\Models\Comment;
use App\Notifications\ActivityNotification;
use Livewire\Component;
use Livewire\WithPagination;

class CommentSection extends Component
{
    use WithPagination;

    public $postId;
    public $content;
    public $comments;
    public $editingCommentId;
    public $editingContent;
    public $replyingToId;

    public function mount($postId)
    {
        $this->postId = $postId;
        $this->loadComments();
    }

    public function loadComments()
    {
        $this->comments = Comment::where('post_id', $this->postId)
            ->whereNull('parent_id')
            ->with(['user', 'replies' => function ($query) {
                $query->with('user');
            }])
            ->latest()
            ->paginate(5);
    }

    public function save()
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
            $post->user->notify(new \App\Notifications\ActivityNotification('comment', auth()->user(), $post));
        }
        $mentionedUsers = $this->parseMentions($this->content);
        foreach ($mentionedUsers as $user) {
            if ($user->id !== auth()->id()) {
                $user->notify(new \App\Notifications\ActivityNotification('mention', auth()->user(), $post));
            }
        }
        $this->content = '';
        $this->replyingToId = null;
        $this->loadComments();
    }

    public function reply($commentId)
    {
        $this->replyingToId = $commentId;
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
        $this->validate(['editingContent' => 'required|max:255']);
        $comment = Comment::where('user_id', auth()->id())->find($this->editingCommentId);
        if ($comment) {
            $comment->update(['content' => $this->editingContent]);
            $this->editingCommentId = null;
            $this->editingContent = '';
            $this->loadComments();
        }
    }

    public function delete($commentId)
    {
        $comment = Comment::where('user_id', auth()->id())->find($commentId);
        if ($comment) {
            $comment->delete();
            $this->loadComments();
        }
    }

    protected function parseMentions($content)
    {
        preg_match_all('/@(\w+)/', $content, $matches);
        return User::whereIn('name', $matches[1])->get();
    }

    public function render()
    {
        return view('livewire.comment-section');
    }

}
