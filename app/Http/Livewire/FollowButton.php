<?php

namespace App\Http\Livewire;

use Livewire\Component;

class FollowButton extends Component
{
    public $userId;
    public $isFollowing;

    public function mount($userId)
    {
        $this->userId = $userId;
        $this->isFollowing = auth()->user()->following()->where('following_id', $this->userId)->exists();
    }

    public function toggleFollow()
    {
        $userToFollow = User::find($this->userId);
        if ($this->isFollowing) {
            auth()->user()->following()->detach($this->userId);
            if ($userToFollow && $userToFollow->id !== auth()->id()) {
                $userToFollow->notify(new \App\Notifications\ActivityNotification('unfollowed', auth()->user(), null));
            }
        } else {
            auth()->user()->following()->attach($this->userId);
            if ($userToFollow && $userToFollow->id !== auth()->id()) {
                $userToFollow->notify(new \App\Notifications\ActivityNotification('followed', auth()->user(), null));
            }
        }
        $this->isFollowing = !$this->isFollowing;
    }

    public function render()
    {
        return view('livewire.follow-button');
    }
}
