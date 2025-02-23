<?php

namespace App\Http\Livewire;

use Livewire\Component;

class ShareButton extends Component
{
    public $postId;
    public $isShared;
    public $shareCount;

    public function mount($postId)
    {
        $this->postId = $postId;
        $this->isShared = auth()->user()->shares()->where('post_id', $postId)->exists();
        $this->shareCount = Share::where('post_id', $postId)->count();
    }

    public function share()
    {
        if ($this->isShared) {
            auth()->user()->shares()->where('post_id', $this->postId)->delete();
        } else {
            auth()->user()->shares()->create(['post_id' => $this->postId]);
            $post = Post::find($this->postId);
            if ($post->user_id !== auth()->id()) {
                $post->user->notify(new \App\Notifications\ActivityNotification('share', auth()->user(), $post));
            }
        }
        $this->isShared = !$this->isShared;
        $this->shareCount = Share::where('post_id', $this->postId)->count();
    }

    public function render()
    {
        return view('livewire.share-button');
    }
}
