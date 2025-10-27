<?php

namespace App\Http\Livewire\Content;

use App\Models\Post;
use App\Models\Reaction;
use App\Notifications\ActivityNotification;
use Livewire\Component;

class ReactionButton extends Component
{
    public $postId;
    public $currentReaction;
    public $reactionCounts;

    /**
     * A map of the available reaction identifiers and their emoji representations.
     *
     * Keeping this list centralized makes it easy to render a consistent set of
     * reactions in the Blade view and to extend the options in the future.
     */
    public $reactionTypes = [
        'like' => '👍',
        'love' => '❤️',
        'haha' => '😂',
        'wow' => '😮',
        'sad' => '😢',
        'angry' => '😡',
    ];

    public function mount($postId)
    {
        $this->postId = $postId;
        $this->loadReaction();
    }

    public function loadReaction()
    {
        // Grab the current user's latest reaction for this post so we can highlight it in the UI.
        $userReaction = auth()->user()->reactions()->where('post_id', $this->postId)->first();
        $this->currentReaction = $userReaction ? $userReaction->type : null;
        // Aggregate reaction counts for each type to show live totals beside every emoji.
        $this->reactionCounts = Reaction::where('post_id', $this->postId)
            ->selectRaw('type, COUNT(*) as count')
            ->groupBy('type')
            ->pluck('count', 'type')
            ->toArray();
    }

    public function react($type)
    {
        // Determine if the user has already reacted to this post and update accordingly.
        $existing = auth()->user()->reactions()->where('post_id', $this->postId)->first();
        if ($existing) {
            if ($existing->type === $type) {
                $existing->delete(); // Unreact if same type
            } else {
                $existing->update(['type' => $type]); // Change reaction
            }
        } else {
            $reaction = auth()->user()->reactions()->create(['post_id' => $this->postId, 'type' => $type]);
            $post = Post::find($this->postId);
            if ($post->user_id !== auth()->id()) {
                $post->user->notify(new \App\Notifications\ActivityNotification('reaction', auth()->user(), $post));
            }
        }
        // Refresh the component state so Livewire updates the UI immediately.
        $this->loadReaction();
    }

    public function render()
    {
        return view('livewire.reaction-button');
    }
}
