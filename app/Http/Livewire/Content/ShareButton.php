<?php

namespace App\Http\Livewire\Content;

use App\Models\Post;
use App\Models\Share;
use App\Notifications\ActivityNotification;
use Livewire\Component;

class ShareButton extends Component
{
    public $postId;
    public $isShared;
    public $shareCount;

    /**
     * Hydrate the component with the share state for the provided post identifier.
     */
    public function mount(int $postId): void
    {
        $this->postId = $postId;
        $this->isShared = auth()->user()->shares()->where('post_id', $postId)->exists();
        $this->shareCount = Share::where('post_id', $postId)->count();
    }

    /**
     * Toggle the authenticated user's share state while dispatching author notifications.
     */
    public function share(): void
    {
        if ($this->isShared) {
            auth()->user()->shares()->where('post_id', $this->postId)->delete();
        } else {
            auth()->user()->shares()->create(['post_id' => $this->postId]);
            $post = Post::find($this->postId);
            // Notify the author when someone other than themselves shares the post.
            if ($post->user_id !== auth()->id()) {
                $post->user->notify(new ActivityNotification('share', auth()->user(), $post));
            }
        }
        $this->isShared = !$this->isShared;
        $this->shareCount = Share::where('post_id', $this->postId)->count();
    }

    /**
     * Provide the Blade view backing the interactive share button control.
     */
    public function render()
    {
        return view('livewire.share-button');
    }
}
