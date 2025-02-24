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
    public $location;

    public function mount()
    {
        $this->bio = auth()->user()->profile->bio;
        $this->avatar = auth()->user()->profile->avatar;
        $this->location = auth()->user()->profile->location;
    }

    public function updateProfile()
    {
        $data = $this->validate([
            'bio' => 'nullable|string|max:255',
            'newAvatar' => 'nullable|image|max:2048',
            'location' => 'nullable|string|max:100',
        ]);

        if ($this->newAvatar) {
            $path = $this->newAvatar->store('avatars', 'public');
            $data['avatar'] = $path;
            $this->avatar = $path;
        } else {
            unset($data['newAvatar']);
        }

        auth()->user()->profile->update($data);
        session()->flash('message', 'Profile updated!');
    }

    public function render()
    {
        return view('livewire.edit-profile')->layout('layouts.app');
    }
}
