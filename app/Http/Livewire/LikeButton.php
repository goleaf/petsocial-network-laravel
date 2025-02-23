<?php

namespace App\Http\Livewire;

use App\Notifications\ActivityNotification;
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
            $like = auth()->user()->likes()->create(['post_id' => $this->postId]);
            $post = Post::find($this->postId);
            if ($post->user_id !== auth()->id()) {
                $post->user->notify(new ActivityNotification('like', auth()->user(), $post));
            }
        }
        $this->isLiked = !$this->isLiked;
        $this->likeCount = Like::where('post_id', $this->postId)->count();
    }

    public function render()
    {
        return view('livewire.like-button');
    }
}
