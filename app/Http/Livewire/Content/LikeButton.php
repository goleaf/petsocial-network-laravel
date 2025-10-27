<?php

namespace App\Http\Livewire\Content;

use App\Models\Like;
use App\Models\Post;
use App\Notifications\ActivityNotification;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class LikeButton extends Component
{
    /**
     * The identifier for the post that owns this like button.
     */
    public int $postId;

    /**
     * Whether the authenticated member has liked the post.
     */
    public bool $isLiked = false;

    /**
     * The total likes currently recorded for the post.
     */
    public int $likeCount = 0;

    /**
     * Bootstrap the component with contextual like state for the acting user.
     */
    public function mount(int $postId): void
    {
        $this->postId = $postId;

        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        // Determine whether the authenticated member has liked the post already.
        if ($user !== null) {
            $this->isLiked = $user->likes()->where('post_id', $postId)->exists();
        }

        // Count the total likes so the UI reflects accurate engagement totals.
        $this->likeCount = Like::query()->where('post_id', $postId)->count();
    }

    /**
     * Toggle the like status and dispatch notifications for post owners when appropriate.
     */
    public function toggleLike(): void
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        // Abort gracefully when the component is rendered without an authenticated member.
        if ($user === null) {
            return;
        }

        if ($this->isLiked) {
            // Remove the like record when toggling off the reaction.
            $user->likes()->where('post_id', $this->postId)->delete();
        } else {
            // Persist a new like entry tied to the acting member and notify the post owner.
            $user->likes()->create(['post_id' => $this->postId]);
            $post = Post::query()->find($this->postId);

            if ($post !== null && $post->user_id !== $user->id && $post->user !== null) {
                // Notify the post owner when someone else reacts to their content.
                $post->user->notify(new ActivityNotification('like', $user, $post));
            }
        }

        // Flip the local state so the interface immediately reflects the change.
        $this->isLiked = ! $this->isLiked;
        $this->likeCount = Like::query()->where('post_id', $this->postId)->count();
    }

    /**
     * Provide the Blade view responsible for rendering the interactive button.
     */
    public function render(): View
    {
        return view('livewire.like-button');
    }
}
