<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;

class Followers extends Component
{
    use WithPagination;

    public $search = '';

    public function render()
    {
        $followers = auth()->user()->followers()
            ->where('name', 'like', "%{$this->search}%")
            ->paginate(10);

        return view('livewire.followers', ['followers' => $followers])
            ->layout('layouts.app');
    }
}
