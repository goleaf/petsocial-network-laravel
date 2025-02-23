<?php

namespace App\Http\Livewire;

use Livewire\Component;

class EditProfile extends Component
{
    public $bio;
    public $avatar;

    public function mount()
    {
        $this->bio = auth()->user()->profile->bio;
        $this->avatar = auth()->user()->profile->avatar;
    }

    public function updateProfile()
    {
        auth()->user()->profile->update([
            'bio' => $this->bio,
            'avatar' => $this->avatar, // Add file upload logic later if needed
        ]);

        session()->flash('message', 'Profile updated!');
    }

    public function render()
    {
        return view('livewire.edit-profile')->layout('layouts.app');
    }
}
