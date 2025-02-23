<?php

namespace App\Http\Livewire;

use Livewire\Component;

class LikeButton extends Component
{
    public $postId;
    public $isLiked;
    public $likeCount;

    public function mount($postId)
    {
        $this->postId = $postId;
        $this->isLiked = auth()->user()->likes()->where('post_id', $postId)->exists();
        $this->likeCount = Like::where('post_id', $postId)->count();
    }

    public function toggleLike()
    {
        if ($this->isLiked) {
            auth()->user()->likes()->where('post_id', $this->postId)->delete();
        } else {
            auth()->user()->likes()->create(['post_id' => $this->postId]);
        }
        $this->isLiked = !$this->isLiked;
        $this->likeCount = Like::where('post_id', $this->postId)->count();
    }

    public function render()
    {
        return view('livewire.like-button');
    }
}
