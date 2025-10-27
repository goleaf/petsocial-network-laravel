<?php

namespace App\Http\Livewire\Content;

use App\Models\Post;
use App\Models\Share;
use App\Notifications\ActivityNotification;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\View\View;
use Livewire\Component;

class ShareButton extends Component
{
    /**
     * Strongly typed identifier for the post being toggled to keep component state predictable.
     */
    public int $postId;

    /**
     * Flag that captures whether the authenticated viewer currently shares the post.
     */
    public bool $isShared = false;

    /**
     * Cached count of total shares so the badge renders without additional queries per request.
     */
    public int $shareCount = 0;

    /**
     * Hydrate the component with the share state for the provided post identifier.
     */
    public function mount(int $postId): void
    {
        $this->postId = $postId;

        /** @var Authenticatable&\App\Models\User $viewer */
        $viewer = auth()->user();

        // Capture the initial share state so the component renders the correct label and badge colour.
        $this->isShared = $viewer->shares()->where('post_id', $postId)->exists();

        // Preload the aggregate share count to avoid recomputing during the initial render.
        $this->shareCount = Share::query()->where('post_id', $postId)->count();
    }

    /**
     * Toggle the authenticated user's share state while dispatching author notifications.
     */
    public function share(): void
    {
        /** @var Authenticatable&\App\Models\User $viewer */
        $viewer = auth()->user();

        if ($this->isShared) {
            // Remove the share when the viewer already shared the post, ensuring idempotent toggles.
            $viewer->shares()->where('post_id', $this->postId)->delete();
        } else {
            // Create the share record and capture the associated post for notification dispatch.
            $viewer->shares()->create(['post_id' => $this->postId]);
            $post = Post::query()->findOrFail($this->postId);
            // Notify the author when someone other than themselves shares the post.
            if ($post->user_id !== $viewer->getAuthIdentifier()) {
                $post->user->notify(new ActivityNotification('share', $viewer, $post));
            }
        }
        $this->isShared = !$this->isShared;
        // Refresh the aggregate so the Livewire badge stays in sync with the latest state.
        $this->shareCount = Share::query()->where('post_id', $this->postId)->count();
    }

    /**
     * Provide the Blade view backing the interactive share button control.
     */
    public function render(): View
    {
        return view('livewire.share-button');
    }
}
