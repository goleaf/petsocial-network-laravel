<?php

namespace App\Http\Livewire;

use App\Models\Post;
use Livewire\Component;

class UserDashboard extends Component
{
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
            ->get();
    }

    public function render()
    {
        return view('livewire.user-dashboard')->layout('layouts.app');
    }
}
