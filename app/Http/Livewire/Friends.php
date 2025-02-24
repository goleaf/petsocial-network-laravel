<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
class Friends extends Component
{
    use WithPagination;

    public $selectedFriends = [];
    public $category;
    public $showCategoryModal = false;

    public function render()
    {
        $friends = auth()->user()->friends()
            ->where('name', 'like', "%{$this->search}%")
            ->withPivot('category')
            ->paginate(10);

        return view('livewire.friends', ['friends' => $friends])
            ->layout('layouts.app');
    }

    public function bulkRemove()
    {
        FriendRequest::where(function ($query) {
            $query->where('sender_id', auth()->id())
                ->whereIn('receiver_id', $this->selectedFriends);
        })->orWhere(function ($query) {
            $query->where('receiver_id', auth()->id())
                ->whereIn('sender_id', $this->selectedFriends);
        })->where('status', 'accepted')->delete();

        $this->selectedFriends = [];
    }

    public function categorizeFriend()
    {
        $this->validate([
            'category' => 'nullable|string|max:50',
        ]);

        FriendRequest::where(function ($query) {
            $query->where('sender_id', auth()->id())
                ->whereIn('receiver_id', $this->selectedFriends);
        })->orWhere(function ($query) {
            $query->where('receiver_id', auth()->id())
                ->whereIn('sender_id', $this->selectedFriends);
        })->where('status', 'accepted')->update(['category' => $this->category]);

        $this->showCategoryModal = false;
        $this->category = null;
        $this->selectedFriends = [];
    }
}
