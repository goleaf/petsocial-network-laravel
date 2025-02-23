<?php

namespace App\Http\Livewire;

use Livewire\Component;

class CreatePost extends Component
{
    public $content;

    public function save()
    {
        $this->validate(['content' => 'required|max:280']);

        auth()->user()->posts()->create(['content' => $this->content]);
        $this->content = '';
        $this->emit('postCreated'); // Refresh feed
    }

    public function render()
    {
        return view('livewire.create-post');
    }
}
