<?php

namespace App\Http\Livewire;

use App\Models\FriendRequest;
use App\Models\User;
use App\Notifications\ActivityNotification;
use Livewire\Component;

class FriendButton extends Component
{
    public $userId;
    public $status;

    public function mount($userId)
    {
        $this->userId = $userId;
        $this->checkStatus();
    }

    public function checkStatus()
    {
        $request = FriendRequest::where(function ($query) {
            $query->where('sender_id', auth()->id())->where('receiver_id', $this->userId);
        })->orWhere(function ($query) {
            $query->where('sender_id', $this->userId)->where('receiver_id', auth()->id());
        })->first();

        $this->status = $request ? $request->status : 'none';
        if ($this->status === 'accepted') {
            $this->status = 'friends';
        }
    }

    public function sendRequest()
    {
        $request = FriendRequest::create([
            'sender_id' => auth()->id(),
            'receiver_id' => $this->userId,
            'status' => 'pending',
        ]);
        $receiver = User::find($this->userId);
        $receiver->notify(new ActivityNotification('friend_request', auth()->user(), null));
        $this->checkStatus();
    }

    public function acceptRequest()
    {
        $request = FriendRequest::where('sender_id', $this->userId)
            ->where('receiver_id', auth()->id())
            ->where('status', 'pending')
            ->first();
        if ($request) {
            $request->update(['status' => 'accepted']);
            $this->checkStatus();
        }
    }

    public function declineRequest()
    {
        $request = FriendRequest::where('sender_id', $this->userId)
            ->where('receiver_id', auth()->id())
            ->where('status', 'pending')
            ->first();
        if ($request) {
            $request->update(['status' => 'declined']);
            $this->checkStatus();
        }
    }

    public function unfriend()
    {
        $request = FriendRequest::where(function ($query) {
            $query->where('sender_id', auth()->id())->where('receiver_id', $this->userId);
        })->orWhere(function ($query) {
            $query->where('sender_id', $this->userId)->where('receiver_id', auth()->id());
        })->where('status', 'accepted')->first();
        if ($request) {
            $request->delete();
            $this->checkStatus();
        }
    }

    public function render()
    {
        return view('livewire.friend-button');
    }
}
