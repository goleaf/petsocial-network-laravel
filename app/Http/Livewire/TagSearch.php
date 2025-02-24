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
        $blockedIds = auth()->user()->blocks->pluck('id');
        $friendIds = auth()->user()->friends->pluck('id');
        $posts = Post::whereHas('tags', function ($query) {
            $query->where('name', 'like', "%{$this->search}%");
        })->where(function ($query) use ($friendIds) {
            $query->whereIn('user_id', $friendIds)->where('posts_visibility', 'friends')
                ->orWhere('posts_visibility', 'public');
        })->whereNotIn('user_id', $blockedIds)
            ->with(['user', 'tags', 'reactions', 'comments'])
            ->latest()
            ->paginate(10);

        return view('livewire.tag-search', ['posts' => $posts])
            ->layout('layouts.app');
    }

}
