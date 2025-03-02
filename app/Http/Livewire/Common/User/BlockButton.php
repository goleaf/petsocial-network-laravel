<?php

namespace App\Http\Livewire\Common\User;

use App\Models\User;
use Livewire\Component;

class BlockButton extends Component
{
    public $userId;
    public $isBlocked = false;

    public function mount($userId)
    {
        $this->userId = $userId;
        $this->checkBlockStatus();
    }

    public function checkBlockStatus()
    {
        $this->isBlocked = auth()->user()->blockedUsers()->where('blocked_user_id', $this->userId)->exists();
    }

    public function toggleBlock()
    {
        $user = User::findOrFail($this->userId);

        if ($this->isBlocked) {
            auth()->user()->blockedUsers()->detach($this->userId);
            $this->isBlocked = false;
            session()->flash('success', "You have unblocked {$user->name}.");
        } else {
            auth()->user()->blockedUsers()->attach($this->userId);
            $this->isBlocked = true;
            session()->flash('success', "You have blocked {$user->name}.");
        }
    }

    public function render()
    {
        return view('livewire.common.user.block-button');
    }
}
