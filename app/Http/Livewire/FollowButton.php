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
        $this->isFollowing = auth()->user()->following()->where('following_id', $userId)->exists();
    }

    public function toggleFollow()
    {
        if ($this->isFollowing) {
            auth()->user()->following()->detach($this->userId);
        } else {
            auth()->user()->following()->attach($this->userId);
        }
        $this->isFollowing = !$this->isFollowing;
    }

    public function render()
    {
        return view('livewire.follow-button');
    }
}
