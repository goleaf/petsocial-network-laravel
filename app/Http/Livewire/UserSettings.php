<?php

namespace App\Http\Livewire;

use Livewire\Component;

class UserSettings extends Component
{
    public $name;
    public $email;
    public $password;
    public $password_confirmation;

    public function mount()
    {
        $this->name = auth()->user()->name;
        $this->email = auth()->user()->email;
    }

    public function update()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . auth()->id(),
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $data = ['name' => $this->name, 'email' => $this->email];
        if ($this->password) {
            $data['password'] = Hash::make($this->password);
        }

        auth()->user()->update($data);
        session()->flash('message', 'Settings updated!');
    }

    public function render()
    {
        return view('livewire.user-settings')->layout('layouts.app');
    }
}
