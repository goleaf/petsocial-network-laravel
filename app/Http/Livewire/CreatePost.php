<?php

namespace App\Http\Livewire;

use Livewire\Component;

class CreatePost extends Component
{
    public $content;
    public $tags = '';
    public $editingPostId;
    public $editingContent;

    public function save()
    {
        $this->validate(['content' => 'required|max:280', 'tags' => 'nullable|string']);
        $post = auth()->user()->posts()->create(['content' => $this->content]);
        $this->attachTags($post);
        $mentionedUsers = $this->parseMentions($this->content);
        foreach ($mentionedUsers as $user) {
            if ($user->id !== auth()->id()) {
                $user->notify(new \App\Notifications\ActivityNotification('mention', auth()->user(), $post));
            }
        }
        $this->content = '';
        $this->tags = '';
        $this->emit('postCreated');
    }

    public function update()
    {
        $this->validate(['editingContent' => 'required|max:280', 'tags' => 'nullable|string']);
        $post = auth()->user()->posts()->find($this->editingPostId);
        if ($post) {
            $post->update(['content' => $this->editingContent]);
            $post->tags()->detach();
            $this->attachTags($post);
            $this->editingPostId = null;
            $this->editingContent = '';
            $this->tags = '';
            $this->emit('postUpdated');
        }
    }

    protected function attachTags($post)
    {
        if ($this->tags) {
            $tagNames = array_filter(array_map('trim', explode(',', $this->tags)));
            foreach ($tagNames as $tagName) {
                $tag = Tag::firstOrCreate(['name' => strtolower($tagName)]);
                $post->tags()->attach($tag->id);
            }
        }
    }

    protected function parseMentions($content)
    {
        preg_match_all('/@(\w+)/', $content, $matches);
        $mentionedUsers = User::whereIn('name', $matches[1])->get();
        return $mentionedUsers;
    }

}
