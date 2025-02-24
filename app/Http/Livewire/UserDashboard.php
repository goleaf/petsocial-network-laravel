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
        $friendIds = auth()->user()->friends->pluck('id')->diff($blockedIds);
        $sharedPostIds = auth()->user()->shares->pluck('post_id');
        $this->posts = Post::where(function ($query) use ($friendIds, $blockedIds) {
            $query->where('posts_visibility', 'public')
                ->orWhere(function ($query) use ($friendIds) {
                    $query->where('posts_visibility', 'friends')->whereIn('user_id', $friendIds);
                })
                ->orWhere('user_id', auth()->id());
        })->whereNotIn('user_id', $blockedIds)
            ->orWhereIn('id', $sharedPostIds)
            ->with(['user', 'comments', 'reactions', 'shares', 'pet'])
            ->latest()
            ->paginate(10);
    }

    public function render()
    {
        return view('livewire.user-dashboard')->layout('layouts.app');
    }

}
