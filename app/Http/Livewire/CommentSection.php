<?php

namespace App\Http\Livewire;

use App\Notifications\ActivityNotification;
use Livewire\Component;
use Livewire\WithPagination;

class CommentSection extends Component
{
    use WithPagination;

    public $postId;
    public $content;
    public $comments;

    public function mount($postId)
    {
        $this->postId = $postId;
        $this->loadComments();
    }

    public function loadComments()
    {
        $this->comments = Comment::where('post_id', $this->postId)
            ->with('user')
            ->latest()
            ->paginate(5); // 5 comments per page
    }

    public function save()
    {
        $this->validate(['content' => 'required|max:255']);

        $comment = Comment::create([
            'user_id' => auth()->id(),
            'post_id' => $this->postId,
            'content' => $this->content,
        ]);

        $post = Post::find($this->postId);
        if ($post->user_id !== auth()->id()) {
            $post->user->notify(new ActivityNotification('comment', auth()->user(), $post));
        }

        $this->content = '';
        $this->loadComments();
    }

    public function render()
    {
        return view('livewire.comment-section');
    }
}
