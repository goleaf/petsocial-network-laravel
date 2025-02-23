<?php

namespace App\Http\Livewire;

use Livewire\Component;

class CreatePost extends Component
{
    public $content;
    public $editingPostId;
    public $editingContent;

    public function save()
    {
        $this->validate(['content' => 'required|max:280']);
        auth()->user()->posts()->create(['content' => $this->content]);
        $this->content = '';
        $this->emit('postCreated');
    }

    public function edit($postId)
    {
        $post = auth()->user()->posts()->find($postId);
        if ($post) {
            $this->editingPostId = $postId;
            $this->editingContent = $post->content;
        }
    }

    public function update()
    {
        $this->validate(['editingContent' => 'required|max:280']);
        $post = auth()->user()->posts()->find($this->editingPostId);
        if ($post) {
            $post->update(['content' => $this->editingContent]);
            $this->editingPostId = null;
            $this->editingContent = '';
            $this->emit('postUpdated');
        }
    }

    public function delete($postId)
    {
        $post = auth()->user()->posts()->find($postId);
        if ($post) {
            $post->delete();
            $this->emit('postDeleted');
        }
    }

    public function render()
    {
        return view('livewire.create-post');
    }
}
