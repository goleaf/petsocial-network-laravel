<?php

namespace App\Http\Livewire\Content;

use App\Models\Post;
use App\Models\Reaction;
use App\Notifications\ActivityNotification;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class ReactionButton extends Component
{
    /**
     * Store the identifier of the post that the component is rendering reactions for.
     */
    public int $postId;

    /**
     * Track the emoji key that represents the viewer's current reaction.
     */
    public ?string $currentReaction = null;

    /**
     * Hold the aggregated counts for every reaction type so the view can show totals.
     */
    public array $reactionCounts = [];

    /**
     * A map of the available reaction identifiers and their emoji representations.
     *
     * Keeping this list centralized makes it easy to render a consistent set of
     * reactions in the Blade view and to extend the options in the future.
     */
    public array $reactionTypes = [
        'like' => '👍',
        'love' => '❤️',
        'haha' => '😂',
        'wow' => '😮',
        'sad' => '😢',
        'angry' => '😡',
    ];

    /**
     * Cache the post instance once it is resolved so repeated lookups are avoided.
     */
    protected ?Post $post = null;

    /**
     * Boot the component by capturing the post identifier and initial reaction state.
     */
    public function mount(int $postId): void
    {
        $this->postId = $postId;
        // Resolve the post once so we can reuse it for notifications later on.
        $this->post = Post::query()->findOrFail($postId);
        $this->loadReaction();
    }

    /**
     * Fetch the viewer's current reaction alongside the aggregated counts.
     */
    public function loadReaction(): void
    {
        $user = $this->authenticatedUser();

        // Grab the current user's latest reaction for this post so we can highlight it in the UI.
        $userReaction = $user?->reactions()->where('post_id', $this->postId)->first();
        $this->currentReaction = $userReaction?->type;

        // Aggregate reaction counts for each type to show live totals beside every emoji.
        $this->reactionCounts = Reaction::where('post_id', $this->postId)
            ->selectRaw('type, COUNT(*) as count')
            ->groupBy('type')
            ->pluck('count', 'type')
            ->toArray();
    }

    /**
     * Toggle or update the viewer's reaction while guarding against invalid requests.
     */
    public function react(string $type): void
    {
        // Refresh the counts and bail early if the requested emoji key is not supported.
        if (!array_key_exists($type, $this->reactionTypes)) {
            $this->loadReaction();

            return;
        }

        $user = $this->authenticatedUser();

        // Guests cannot react, so refresh the component and exit without touching the database.
        if ($user === null) {
            $this->loadReaction();

            return;
        }

        // Determine if the user has already reacted to this post and update accordingly.
        $existing = $user->reactions()->where('post_id', $this->postId)->first();
        if ($existing) {
            if ($existing->type === $type) {
                $existing->delete(); // Unreact if same type
            } else {
                $existing->update(['type' => $type]); // Change reaction
            }
        } else {
            $user->reactions()->create(['post_id' => $this->postId, 'type' => $type]);
            $post = $this->post ?? Post::query()->findOrFail($this->postId);

            // Only notify the author when somebody else reacts to their post.
            if ($post->user_id !== $user->getAuthIdentifier()) {
                $post->user->notify(new ActivityNotification('reaction', $user, $post));
            }
        }
        // Refresh the component state so Livewire updates the UI immediately.
        $this->loadReaction();
    }

    /**
     * Provide the Blade view that renders the interactive reaction selector.
     */
    public function render(): View
    {
        return view('livewire.reaction-button');
    }

    /**
     * Resolve the authenticated user so reaction logic stays centralised.
     */
    protected function authenticatedUser(): ?Authenticatable
    {
        return Auth::user();
    }
}
