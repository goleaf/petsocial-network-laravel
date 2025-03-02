<?php

namespace App\Http\Livewire;

use App\Models\Friendship;
use App\Models\User;
use Livewire\Component;

class FriendButton extends Component
{
    public $userId;
    public $status;
    public $category;
    
    protected $listeners = ['refresh' => '$refresh'];

    public function mount($userId)
    {
        $this->userId = $userId;
        $this->refreshStatus();
    }

    public function refreshStatus()
    {
        $user = User::find($this->userId);
        
        if (!$user || $user->id === auth()->id()) {
            $this->status = 'self';
            return;
        }
        
        $friendship = Friendship::where(function ($query) {
            $query->where('sender_id', auth()->id())
                  ->where('recipient_id', $this->userId);
        })->orWhere(function ($query) {
            $query->where('sender_id', $this->userId)
                  ->where('recipient_id', auth()->id());
        })->first();

        if (!$friendship) {
            $this->status = 'none';
            return;
        }
        
        if ($friendship->status === 'pending') {
            if ($friendship->sender_id === auth()->id()) {
                $this->status = 'sent_request';
            } else {
                $this->status = 'received_request';
            }
        } else if ($friendship->status === 'accepted') {
            $this->status = 'friends';
            $this->category = $friendship->category;
        } else if ($friendship->status === 'blocked') {
            if ($friendship->sender_id === auth()->id()) {
                $this->status = 'blocked';
            } else {
                $this->status = 'none'; // Don't show if user is blocked by other user
            }
        }
    }

    public function sendRequest()
    {
        $user = User::find($this->userId);
        
        if (!$user || $user->id === auth()->id()) {
            return;
        }
        
        // Create a new friendship request
        $friendship = Friendship::create([
            'sender_id' => auth()->id(),
            'recipient_id' => $this->userId,
            'status' => 'pending',
        ]);
        
        // Create notification for the recipient
        $user->notifications()->create([
            'type' => 'friend_request',
            'notifiable_type' => User::class,
            'notifiable_id' => auth()->id(),
            'data' => [
                'message' => auth()->user()->name . ' sent you a friend request',
                'friendship_id' => $friendship->id,
            ],
            'priority' => 'high',
        ]);
        
        $this->refreshStatus();
        $this->emit('refresh');
    }

    public function acceptRequest()
    {
        $friendship = Friendship::where('sender_id', $this->userId)
                               ->where('recipient_id', auth()->id())
                               ->where('status', 'pending')
                               ->first();
                               
        if ($friendship) {
            $friendship->accept();
            $this->refreshStatus();
            $this->emit('refresh');
        }
    }

    public function declineRequest()
    {
        $friendship = Friendship::where('sender_id', $this->userId)
                               ->where('recipient_id', auth()->id())
                               ->where('status', 'pending')
                               ->first();
                               
        if ($friendship) {
            $friendship->decline();
            $this->refreshStatus();
            $this->emit('refresh');
        }
    }
    
    public function cancelRequest()
    {
        $friendship = Friendship::where('sender_id', auth()->id())
                               ->where('recipient_id', $this->userId)
                               ->where('status', 'pending')
                               ->first();
                               
        if ($friendship) {
            $friendship->delete();
            $this->refreshStatus();
            $this->emit('refresh');
        }
    }

    public function removeFriend()
    {
        $friendship = Friendship::where(function ($query) {
            $query->where('sender_id', auth()->id())
                  ->where('recipient_id', $this->userId);
        })->orWhere(function ($query) {
            $query->where('sender_id', $this->userId)
                  ->where('recipient_id', auth()->id());
        })->where('status', 'accepted')->first();
        
        if ($friendship) {
            $friendship->delete();
            $this->refreshStatus();
            $this->emit('refresh');
        }
    }
    
    public function blockUser()
    {
        $user = User::find($this->userId);
        
        if (!$user || $user->id === auth()->id()) {
            return;
        }
        
        // Remove any existing friendship
        Friendship::where(function ($query) {
            $query->where('sender_id', auth()->id())
                  ->where('recipient_id', $this->userId);
        })->orWhere(function ($query) {
            $query->where('sender_id', $this->userId)
                  ->where('recipient_id', auth()->id());
        })->delete();
        
        // Create a blocked relationship
        Friendship::create([
            'sender_id' => auth()->id(),
            'recipient_id' => $this->userId,
            'status' => 'blocked',
        ]);
        
        $this->refreshStatus();
        $this->emit('refresh');
    }
    
    public function unblockUser()
    {
        $friendship = Friendship::where('sender_id', auth()->id())
                               ->where('recipient_id', $this->userId)
                               ->where('status', 'blocked')
                               ->first();
                               
        if ($friendship) {
            $friendship->delete();
            $this->refreshStatus();
            $this->emit('refresh');
        }
    }

    public function render()
    {
        return view('livewire.friend-button');
    }
}
