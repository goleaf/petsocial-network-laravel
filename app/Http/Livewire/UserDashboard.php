<?php

namespace App\Http\Livewire;

use App\Models\Post;
use Livewire\Component;
use Livewire\WithPagination;

class UserDashboard extends Component
{
    use WithPagination;

    public $posts;

    public function mount()
    {
        $this->loadPosts();
    }

    public function loadPosts()
    {
        $followingIds = auth()->user()->following->pluck('id');
        $this->posts = Post::whereIn('user_id', $followingIds)
            ->orWhere('user_id', auth()->id())
            ->with(['user', 'comments', 'likes'])
            ->latest()
            ->paginate(10); // 10 posts per page
    }

    public function render()
    {
        return view('livewire.user-dashboard')->layout('layouts.app');
    }
}
