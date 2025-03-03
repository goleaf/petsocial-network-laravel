<?php

namespace App\Http\Livewire\Content;

use Livewire\Component;
use App\Models\Post;
use App\Notifications\ActivityNotification;
use Illuminate\Support\Facades\Validator;

class CreatePost extends Component
{
    public $content;
    public $tags = '';
    public $pet_id;
    public $editingPostId;
    public $editingContent;
    public $visibility = 'public'; // Add visibility option
    public $images = []; // Add image upload support
    
    protected $rules = [
        'content' => 'required|max:1000', // Increased character limit
        'tags' => 'nullable|string|max:255',
        'pet_id' => 'nullable|exists:pets,id',
        'visibility' => 'required|in:public,friends,private',
    ];

    protected $messages = [
        'content.required' => 'Post content cannot be empty',
        'content.max' => 'Post content cannot exceed 1000 characters',
        'pet_id.exists' => 'Selected pet does not exist',
        'visibility.in' => 'Invalid visibility option selected',
    ];

    public function save()
    {
        // Validate with custom check for pet ownership
        $this->validate();
        $this->validatePetOwnership();
        
        $post = auth()->user()->posts()->create([
            'content' => $this->content,
            'pet_id' => $this->pet_id,
            'visibility' => $this->visibility,
        ]);
        
        $this->attachTags($post);
        $this->handleMentions($post);
        $this->createActivityLog('post_created');
        $this->resetFields();
        
        $this->emit('postCreated'); // Emit event for other components
    }

    public function update()
    {
        // Validate post update
        $this->validate([
            'editingContent' => 'required|max:1000',
            'tags' => 'nullable|string|max:255',
        ]);
        
        $post = Post::findOrFail($this->editingPostId);
        
        // Check if user owns the post
        if ($post->user_id !== auth()->id()) {
            return $this->addError('editingContent', 'You cannot edit this post');
        }
        
        $post->update([
            'content' => $this->editingContent,
        ]);
        
        $this->attachTags($post);
        $this->createActivityLog('post_updated');
        $this->resetEditingState();
        
        $this->emit('postUpdated'); // Emit event for other components
    }
    
    protected function validatePetOwnership()
    {
        // Only validate if pet_id is provided
        if ($this->pet_id) {
            $validator = Validator::make(
                ['pet_id' => $this->pet_id],
                ['pet_id' => 'exists:pets,id,user_id,' . auth()->id()]
            );
            
            if ($validator->fails()) {
                $this->addError('pet_id', 'You can only post as your own pets');
                throw new \Illuminate\Validation\ValidationException($validator);
            }
        }
    }
    
    protected function handleMentions($post)
    {
        $mentionedUsers = $this->parseMentions($this->content);
        foreach ($mentionedUsers as $user) {
            if ($user->id !== auth()->id()) {
                $user->notify(new ActivityNotification('mention', auth()->user(), $post));
            }
        }
    }
    
    protected function createActivityLog($action)
    {
        $description = $action === 'post_created' 
            ? "Created a post: " . substr($this->content, 0, 50) . (strlen($this->content) > 50 ? '...' : '')
            : "Updated a post: " . substr($this->editingContent, 0, 50) . (strlen($this->editingContent) > 50 ? '...' : '');
            
        auth()->user()->activityLogs()->create([
            'action' => $action,
            'description' => $description,
        ]);
    }
    
    protected function resetFields()
    {
        $this->content = '';
        $this->tags = '';
        $this->pet_id = null;
        $this->visibility = 'public';
        $this->images = [];
    }
    
    protected function resetEditingState()
    {
        $this->editingPostId = null;
        $this->editingContent = '';
        $this->tags = '';
    }

    public function render()
    {
        return view('livewire.create-post', [
            'pets' => auth()->user()->pets
        ]);
    }
}