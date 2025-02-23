<?php

namespace App\Http\Livewire;

use App\Models\Post;
use Livewire\Component;
use Livewire\WithPagination;

class UserDashboard extends Component
{
    use WithPagination;

    public $posts;

    protected $listeners = ['postCreated' => 'loadPosts', 'postUpdated' => 'loadPosts', 'postDeleted' => 'loadPosts'];

    public function mount()
    {
        $this->loadPosts();
    }

    public function loadPosts()
    {
        $blockedIds = auth()->user()->blocks->pluck('id');
        $followingIds = auth()->user()->following->pluck('id')->diff($blockedIds);
        $sharedPostIds = auth()->user()->shares->pluck('post_id');
        $this->posts = Post::whereIn('user_id', $followingIds)
            ->orWhere('user_id', auth()->id())
            ->orWhereIn('id', $sharedPostIds)
            ->whereNotIn('user_id', $blockedIds)
            ->with(['user', 'comments', 'reactions', 'shares'])
            ->latest()
            ->paginate(10);
    }

    public function render()
    {
        return view('livewire.user-dashboard')->layout('layouts.app');
    }
}
