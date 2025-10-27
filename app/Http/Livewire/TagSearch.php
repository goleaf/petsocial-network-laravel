<?php

namespace App\Http\Livewire;

use App\Models\Post;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class TagSearch extends Component
{
    use WithPagination;

    public $search = '';

    public function render(): View
    {
        // Resolve the authenticated viewer once so downstream lookups avoid repeated auth() calls.
        $viewer = auth()->user();

        // Collect IDs for blocked accounts to ensure their content never surfaces in search results.
        $blockedIds = $viewer->blocks->pluck('id')->all();

        // Gather the viewer's friend IDs to allow friends-only posts from trusted connections.
        $friendIds = $viewer->getFriendIds();

        // Cache the viewer ID for reuse in the visibility clause below.
        $viewerId = $viewer->getKey();

        $posts = Post::whereHas('tags', function ($query) {
            // Filter posts whose attached tag names partially match the search term.
            $query->where('name', 'like', "%{$this->search}%");
        })->where(function ($query) use ($friendIds, $viewerId) {
            // Allow public posts, posts from friends when the visibility is friends-only, and the viewer's own posts.
            $query->where('posts_visibility', 'public')
                ->orWhere(function ($query) use ($friendIds) {
                    $query->where('posts_visibility', 'friends')->whereIn('user_id', $friendIds);
                })
                ->orWhere('user_id', $viewerId);
        })->whereNotIn('user_id', $blockedIds)
            ->with(['user', 'tags', 'reactions', 'comments', 'pet'])
            ->latest()
            ->paginate(10);

        return view('livewire.tag-search', ['posts' => $posts])
            ->layout('layouts.app');
    }

}
