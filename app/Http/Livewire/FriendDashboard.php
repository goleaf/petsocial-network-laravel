<?php

namespace App\Http\Livewire;

use App\Models\User;
use App\Models\Friendship;
use App\Models\Follow;
use Livewire\Component;

class FriendDashboard extends Component
{
    public $pendingRequestsCount = 0;
    public $friendsCount = 0;
    public $followersCount = 0;
    public $followingCount = 0;
    public $recentFriendships = [];
    public $recentFollows = [];
    
    protected $listeners = ['refresh' => '$refresh'];
    
    public function mount()
    {
        $this->loadStats();
    }
    
    public function loadStats()
    {
        $currentUser = auth()->user();
        
        // Count stats
        $this->pendingRequestsCount = $currentUser->pendingFriendRequests()->count();
        $this->friendsCount = $currentUser->friends()->count();
        $this->followersCount = $currentUser->followers()->count();
        $this->followingCount = $currentUser->following()->count();
        
        // Get recent friendships (last 5)
        $this->recentFriendships = Friendship::where('status', 'accepted')
            ->where(function($query) use ($currentUser) {
                $query->where('sender_id', $currentUser->id)
                    ->orWhere('recipient_id', $currentUser->id);
            })
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function($friendship) use ($currentUser) {
                $otherUserId = $friendship->sender_id == $currentUser->id 
                    ? $friendship->recipient_id 
                    : $friendship->sender_id;
                
                return [
                    'user' => User::find($otherUserId),
                    'date' => $friendship->updated_at,
                    'category' => $friendship->category
                ];
            });
            
        // Get recent follows (last 5)
        $this->recentFollows = Follow::where('follower_id', $currentUser->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function($follow) {
                return [
                    'user' => User::find($follow->followed_id),
                    'date' => $follow->created_at,
                    'notify' => $follow->notify
                ];
            });
    }
    
    public function render()
    {
        return view('livewire.friend-dashboard');
    }
}
