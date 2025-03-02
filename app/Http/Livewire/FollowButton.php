<?php

namespace App\Http\Livewire;

use App\Models\Follow;
use App\Models\User;
use Livewire\Component;

class FollowButton extends Component
{
    public $userId;
    public $isFollowing;
    public $notificationsEnabled;
    public $showNotificationToggle = false;
    
    protected $listeners = ['refresh' => '$refresh'];

    public function mount($userId)
    {
        $this->userId = $userId;
        $this->refreshStatus();
    }
    
    public function refreshStatus()
    {
        $follow = Follow::where('follower_id', auth()->id())
                        ->where('followed_id', $this->userId)
                        ->first();
                        
        $this->isFollowing = $follow ? true : false;
        $this->notificationsEnabled = $follow ? $follow->notify : false;
        $this->showNotificationToggle = $this->isFollowing;
    }

    public function toggleFollow()
    {
        $userToFollow = User::find($this->userId);
        
        if (!$userToFollow || $userToFollow->id === auth()->id()) {
            return;
        }
        
        if ($this->isFollowing) {
            // Unfollow
            Follow::where('follower_id', auth()->id())
                  ->where('followed_id', $this->userId)
                  ->delete();
                  
            // Create notification for the unfollowed user
            $userToFollow->notifications()->create([
                'type' => 'unfollowed',
                'notifiable_type' => User::class,
                'notifiable_id' => auth()->id(),
                'data' => [
                    'message' => auth()->user()->name . ' unfollowed you',
                ],
                'priority' => 'low',
            ]);
        } else {
            // Follow
            Follow::create([
                'follower_id' => auth()->id(),
                'followed_id' => $this->userId,
                'notify' => true,
            ]);
            
            // Create notification for the followed user
            $userToFollow->notifications()->create([
                'type' => 'new_follower',
                'notifiable_type' => User::class,
                'notifiable_id' => auth()->id(),
                'data' => [
                    'message' => auth()->user()->name . ' started following you',
                ],
                'priority' => 'normal',
            ]);
        }
        
        $this->refreshStatus();
        $this->emit('refresh');
    }
    
    public function toggleNotifications()
    {
        $follow = Follow::where('follower_id', auth()->id())
                        ->where('followed_id', $this->userId)
                        ->first();
                        
        if ($follow) {
            $follow->update(['notify' => !$follow->notify]);
            $this->notificationsEnabled = !$this->notificationsEnabled;
            $this->emit('refresh');
        }
    }

    public function render()
    {
        return view('livewire.follow-button');
    }
}
