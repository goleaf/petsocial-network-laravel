<?php

namespace App\Http\Livewire;

use Livewire\Component;

class AdminManageUsers extends Component
{
    public $users;

    public function mount()
    {
        $this->loadUsers();
    }

    public function loadUsers()
    {
        $this->users = User::where('id', '!=', auth()->id())->get();
    }

    public function deleteUser($userId)
    {
        User::find($userId)->delete();
        $this->loadUsers();
    }

    public function render()
    {
        return view('livewire.admin-manage-users')->layout('layouts.app');
    }
}
