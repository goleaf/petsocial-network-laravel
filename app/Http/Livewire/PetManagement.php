<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;

class PetManagement extends Component
{
    use WithFileUploads;

    public $pets;
    public $name;
    public $type;
    public $breed;
    public $birthdate;
    public $avatar;

    public function mount()
    {
        $this->pets = auth()->user()->pets;
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'type' => 'nullable|string|max:255',
            'breed' => 'nullable|string|max:255',
            'birthdate' => 'nullable|date',
            'avatar' => 'nullable|image|max:2048',
        ]);

        $data = [
            'user_id' => auth()->id(),
            'name' => $this->name,
            'type' => $this->type,
            'breed' => $this->breed,
            'birthdate' => $this->birthdate,
        ];

        if ($this->avatar) {
            $data['avatar'] = $this->avatar->store('pet-avatars', 'public');
        }

        auth()->user()->pets()->create($data);
        $this->reset(['name', 'type', 'breed', 'birthdate', 'avatar']);
        $this->pets = auth()->user()->pets;
    }

    public function delete($petId)
    {
        $pet = auth()->user()->pets()->find($petId);
        if ($pet) {
            $pet->delete();
            $this->pets = auth()->user()->pets;
        }
    }

    public function render()
    {
        return view('livewire.pet-management')->layout('layouts.app');
    }
}
