<?php

namespace App\Http\Livewire;

use App\Notifications\ActivityNotification;
use Livewire\Component;

class ReactionButton extends Component
{
    public $postId;
    public $currentReaction;
    public $reactionCounts;

    public $reactionTypes = [
        'like' => '👍',
        'love' => '❤️',
        'haha' => '😂',
    ];

    public function mount($postId)
    {
        $this->postId = $postId;
        $this->loadReaction();
    }

    public function loadReaction()
    {
        $userReaction = auth()->user()->reactions()->where('post_id', $this->postId)->first();
        $this->currentReaction = $userReaction ? $userReaction->type : null;
        $this->reactionCounts = Reaction::where('post_id', $this->postId)
            ->selectRaw('type, COUNT(*) as count')
            ->groupBy('type')
            ->pluck('count', 'type')
            ->toArray();
    }

    public function react($type)
    {
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
        $this->loadReaction();
    }

    public function render()
    {
        return view('livewire.reaction-button');
    }
}
