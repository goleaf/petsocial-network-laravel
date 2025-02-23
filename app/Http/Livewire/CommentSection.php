<?php

namespace App\Http\Livewire;

use Livewire\Component;

class CommentSection extends Component
{
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
        $this->comments = Comment::where('post_id', $this->postId)->with('user')->latest()->get();
    }

    public function save()
    {
        $this->validate(['content' => 'required|max:255']);

        Comment::create([
            'user_id' => auth()->id(),
            'post_id' => $this->postId,
            'content' => $this->content,
        ]);

        $this->content = '';
        $this->loadComments();
    }

    public function render()
    {
        return view('livewire.comment-section');
    }
}
