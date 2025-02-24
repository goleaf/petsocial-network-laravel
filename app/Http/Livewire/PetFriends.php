<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;

class PetFriends extends Component
{
    use WithPagination;

    public $petId;
    public $search = '';
    public $selectedFriends = [];
    public $category;

    public function mount($petId)
    {
        $this->petId = $petId;
    }

    public function addFriend($friendPetId)
    {
        PetFriendship::create([
            'pet_id' => $this->petId,
            'friend_pet_id' => $friendPetId,
        ]);
    }

    public function removeFriends()
    {
        PetFriendship::where('pet_id', $this->petId)
            ->whereIn('friend_pet_id', $this->selectedFriends)
            ->orWhere(function ($query) {
                $query->where('friend_pet_id', $this->petId)
                    ->whereIn('pet_id', $this->selectedFriends);
            })->delete();
        $this->selectedFriends = [];
    }

    public function categorizeFriends()
    {
        $this->validate(['category' => 'nullable|string|max:50']);
        PetFriendship::where('pet_id', $this->petId)
            ->whereIn('friend_pet_id', $this->selectedFriends)
            ->orWhere(function ($query) {
                $query->where('friend_pet_id', $this->petId)
                    ->whereIn('pet_id', $this->selectedFriends);
            })->update(['category' => $this->category]);
        $this->category = null;
        $this->selectedFriends = [];
    }

    public function render()
    {
        $pet = Pet::findOrFail($this->petId);
        if ($pet->user_id !== auth()->id()) {
            abort(403);
        }
        $friends = $pet->allFriends()
            ->where('name', 'like', "%{$this->search}%")
            ->paginate(10);
        $suggestions = Pet::where('user_id', '!=', auth()->id())
            ->whereNotIn('id', $pet->allFriends()->pluck('id')->push($this->petId))
            ->where('name', 'like', "%{$this->search}%")
            ->inRandomOrder()
            ->limit(5)
            ->get();

        return view('livewire.pet-friends', compact('pet', 'friends', 'suggestions'))
            ->layout('layouts.app');
    }
}
