<?php

namespace App\Http\Livewire;

use App\Models\User;
use App\Models\Post;
use App\Models\Comment;
use App\Models\Reaction;
use App\Models\Share;
use App\Models\FriendRequest;
use Livewire\Component;

class AdminAnalytics extends Component
{
    public $userCount;
    public $postCount;
    public $commentCount;
    public $reactionCount;
    public $shareCount;
    public $topUsers;

    public function mount()
    {
        $this->loadAnalytics();
    }

    public function loadAnalytics()
    {
        $this->userCount = User::count();
        $this->postCount = Post::count();
        $this->commentCount = Comment::count();
        $this->reactionCount = Reaction::count();
        $this->shareCount = Share::count();
        $this->friendCount = FriendRequest::where('status', 'accepted')->count() / 2; // Divide by 2 since mutual
        $this->topUsers = User::withCount('posts')
            ->orderBy('posts_count', 'desc')
            ->limit(5)
            ->get();
    }

    public function render()
    {
        return view('livewire.admin-analytics')->layout('layouts.app');
    }
}
