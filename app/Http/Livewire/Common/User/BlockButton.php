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
        // Use the pivot column defined on the blocks table so the cached flag mirrors database reality.
        $this->isBlocked = auth()->user()->blockedUsers()->where('blocked_id', $this->userId)->exists();
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
        // Share the computed state with the Blade view so assertions can verify toggle visibility.
        return view('livewire.common.user.block-button', [
            'isBlocked' => $this->isBlocked,
        ]);
    }
}
