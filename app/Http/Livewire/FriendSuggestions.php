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
        $friendIds = auth()->user()->friends->pluck('id')->push(auth()->id());
        $blockedIds = auth()->user()->blocks->pluck('id');
        $this->suggestions = User::whereNotIn('id', $friendIds)
            ->whereNotIn('id', $blockedIds)
            ->whereHas('friends', function ($query) use ($friendIds) {
                $query->whereIn('id', $friendIds);
            }) // Friends of friends
            ->inRandomOrder()
            ->limit(5)
            ->get();
    }

    public function render()
    {
        return view('livewire.friend-suggestions');
    }
}
