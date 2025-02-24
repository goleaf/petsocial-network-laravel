<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;

class Friends extends Component
{
    use WithPagination;

    public $search = '';

    public function render()
    {
        $friends = auth()->user()->friends()
            ->where('name', 'like', "%{$this->search}%")
            ->paginate(10);

        return view('livewire.friends', ['friends' => $friends])
            ->layout('layouts.app');
    }
}
