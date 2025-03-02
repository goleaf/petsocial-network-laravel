<?php

namespace App\Http\Livewire;

use App\Models\User;
use Livewire\Component;

class FriendSuggestions extends Component
{
    public $suggestions;

    public function mount()
    {
        $this->loadSuggestions();
    }

    public function loadSuggestions()
    {
        $friendIds = auth()->user()->friends()->pluck('users.id')->push(auth()->id());
        // Explicitly pluck 'users.id' from the blocks relationship
        $blockedIds = optional(auth()->user()->blocks())->pluck('users.id') ?? collect();

        $this->suggestions = User::whereNotIn('id', $friendIds)
            ->whereNotIn('id', $blockedIds)
            ->whereHas('friends', function ($query) use ($friendIds) {
                $query->whereIn('users.id', $friendIds); // Friends of friends
            })
            ->inRandomOrder()
            ->limit(5)
            ->get();
    }

    public function render()
    {
        return view('livewire.friend-suggestions');
    }
}
