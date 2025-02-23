<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;

class EditProfile extends Component
{
    use WithFileUploads;

    public $bio;
    public $avatar;
    public $newAvatar;

    public function mount()
    {
        $this->bio = auth()->user()->profile->bio;
        $this->avatar = auth()->user()->profile->avatar;
    }

    public function updateProfile()
    {
        $data = $this->validate([
            'bio' => 'nullable|string|max:255',
            'newAvatar' => 'nullable|image|max:2048', // Max 2MB
        ]);

        if ($this->newAvatar) {
            $path = $this->newAvatar->store('avatars', 'public');
            $data['avatar'] = $path;
            $this->avatar = $path;
        }

        auth()->user()->profile->update($data);
        session()->flash('message', 'Profile updated!');
    }

    public function render()
    {
        return view('livewire.edit-profile')->layout('layouts.app');
    }
}
