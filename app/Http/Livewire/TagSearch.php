<?php

namespace App\Http\Livewire;

use App\Models\Post;
use Livewire\Component;
use Livewire\WithPagination;

class TagSearch extends Component
{
    use WithPagination;

    public $search = '';

    public function render()
    {
        // Get blocked IDs
        $blockedIds = auth()->user()->blocks->pluck('id')->toArray() ?? [];
        
        // Get friend IDs using the helper method
        $friendIds = auth()->user()->getFriendIds();
        
        $posts = Post::whereHas('tags', function ($query) {
            $query->where('name', 'like', "%{$this->search}%");
        })->where(function ($query) use ($friendIds) {
            $query->where('posts_visibility', 'public')
                ->orWhere(function ($query) use ($friendIds) {
                    $query->where('posts_visibility', 'friends')->whereIn('user_id', $friendIds);
                })
                ->orWhere('user_id', auth()->id());
        })->whereNotIn('user_id', $blockedIds)
            ->with(['user', 'tags', 'reactions', 'comments', 'pet'])
            ->latest()
            ->paginate(10);

        return view('livewire.tag-search', ['posts' => $posts])
            ->layout('layouts.app');
    }

}
