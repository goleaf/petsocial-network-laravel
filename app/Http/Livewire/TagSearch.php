<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;

class TagSearch extends Component
{
    use WithPagination;

    public $search = '';

    public function render()
    {
        $posts = Post::whereHas('tags', function ($query) {
            $query->where('name', 'like', "%{$this->search}%");
        })->with(['user', 'tags', 'reactions', 'comments'])
            ->latest()
            ->paginate(10);

        return view('livewire.tag-search', ['posts' => $posts])
            ->layout('layouts.app');
    }
}
