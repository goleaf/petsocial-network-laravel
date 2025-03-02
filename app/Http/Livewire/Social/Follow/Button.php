<?php

namespace App\Http\Livewire\Social\Follow;

use App\Models\Follow;
use App\Models\User;
use Livewire\Component;

class Button extends Component
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
                        
        $this->isFollowing = (bool) $follow;
        $this->notificationsEnabled = $follow ? $follow->notify : false;
        $this->showNotificationToggle = $this->isFollowing;
    }
    
    public function toggleFollow()
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }
        
        $userToFollow = User::find($this->userId);
        
        if (!$userToFollow) {
            return;
        }
        
        $follow = Follow::where('follower_id', auth()->id())
                        ->where('followed_id', $this->userId)
                        ->first();
                        
        if ($follow) {
            $follow->delete();
        } else {
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
        return view('livewire.follow.button');
    }
}
