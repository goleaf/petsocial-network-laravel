<?php

namespace App\Http\Livewire\Content;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use App\Notifications\ActivityNotification;
use Illuminate\Support\Facades\Validator;

class CreatePost extends Component
{
    use WithFileUploads;
    
    // Base properties
    public $content = '';
    public $tags = '';
    public $pet_id;
    public $editingPostId;
    public $editingContent;
    public $visibility = 'public';
    
    // Image upload properties
    public $images = [];
    public $temporaryImages = [];
    public $uploading = false;
    
    // Mention system properties
    public $mentionQuery = '';
    public $mentionResults = [];
    public $showMentionDropdown = false;
    public $mentionPosition = 0;
    
    // Draft functionality properties
    public $draftMode = false;
    public $draftId = null;
    public $autoSaveInterval = 30; // seconds
    
    // Tag system properties
    public $popularTags = [];
    public $showTagDropdown = false;
    public $tagQuery = '';
    public $matchingTags = [];
    
    // Content validation properties
    public $contentLength = 0;
    public $maxLength = 1000;
    public $detectLinks = true;
    public $contentWarnings = [];
    
    protected $rules = [
        'content' => 'required|max:1000',
        'tags' => 'nullable|string|max:255',
        'pet_id' => 'nullable|exists:pets,id',
        'visibility' => 'required|in:public,friends,private',
        'images.*' => 'nullable|image|max:5120|mimes:jpg,jpeg,png,gif',
    ];

    protected $messages = [
        'content.required' => 'Post content cannot be empty',
        'content.max' => 'Post content cannot exceed 1000 characters',
        'pet_id.exists' => 'Selected pet does not exist',
        'visibility.in' => 'Invalid visibility option selected',
        'images.*.image' => 'Uploaded file must be an image',
        'images.*.max' => 'Image size cannot exceed 5MB',
        'images.*.mimes' => 'Image must be a jpg, jpeg, png or gif file',
    ];
    
    protected $listeners = [
        'autoSaveDraft',
        'cancelEdit' => 'resetEditingState'
    ];

    public function mount()
    {
        $this->loadPopularTags();
        $this->loadDraft();
    }
    
    // Image upload methods
    public function updatedImages()
    {
        $this->validate([
            'images.*' => 'image|max:5120|mimes:jpg,jpeg,png,gif',
        ]);
        
        $this->temporaryImages = [];
        foreach($this->images as $image) {
            $this->temporaryImages[] = [
                'url' => $image->temporaryUrl(),
                'name' => $image->getClientOriginalName()
            ];
        }
    }
    
    public function removeImage($index)
    {
        array_splice($this->temporaryImages, $index, 1);
        $newImages = [];
        foreach($this->images as $i => $image) {
            if($i != $index) {
                $newImages[] = $image;
            }
        }
        $this->images = $newImages;
    }
    
    // Content tracking methods
    public function updatedContent($value)
    {
        $this->detectMentions($value);
        $this->validateContentFormat($value);
        $this->contentLength = strlen($value);
        
        // Auto-save draft if content has substance
        if (strlen($value) > 20 && !$this->editingPostId) {
            $this->autoSaveDraft();
        }
    }
    
    // Mention system methods
    protected function detectMentions($text)
    {
        $lastAtPos = strrpos($text, '@');
        if ($lastAtPos !== false && (
            $lastAtPos === 0 || 
            in_array($text[$lastAtPos-1] ?? '', [' ', "\n"])
        )) {
            $this->mentionPosition = $lastAtPos;
            $query = substr($text, $lastAtPos + 1);
            $nextSpace = strpos($query, ' ');
            
            if ($nextSpace !== false) {
                $this->mentionQuery = substr($query, 0, $nextSpace);
            } else {
                $this->mentionQuery = $query;
            }
            
            if (strlen($this->mentionQuery) > 0) {
                $this->searchUsers();
                $this->showMentionDropdown = true;
            } else {
                $this->showMentionDropdown = false;
            }
        } else {
            $this->showMentionDropdown = false;
        }
    }
    
    protected function searchUsers()
    {
        $this->mentionResults = User::where('name', 'like', "%{$this->mentionQuery}%")
            ->orWhere('username', 'like', "%{$this->mentionQuery}%")
            ->limit(5)
            ->get(['id', 'name', 'username', 'profile_photo_path'])
            ->toArray();
    }
    
    public function selectMention($username)
    {
        $beforeMention = substr($this->content, 0, $this->mentionPosition);
        $afterMention = substr($this->content, $this->mentionPosition + strlen('@' . $this->mentionQuery));
        $this->content = $beforeMention . '@' . $username . ' ' . $afterMention;
        $this->showMentionDropdown = false;
    }
    
    // Tag system methods
    protected function loadPopularTags()
    {
        $this->popularTags = Tag::withCount('posts')
            ->orderBy('posts_count', 'desc')
            ->limit(10)
            ->pluck('name')
            ->toArray();
    }
    
    public function updatedTags($value)
    {
        $lastCommaPos = strrpos($value, ',');
        if ($lastCommaPos !== false) {
            $this->tagQuery = trim(substr($value, $lastCommaPos + 1));
            if (strlen($this->tagQuery) > 0) {
                $this->searchTags();
                $this->showTagDropdown = true;
            } else {
                $this->showTagDropdown = false;
            }
        } else {
            $this->tagQuery = trim($value);
            if (strlen($this->tagQuery) > 0) {
                $this->searchTags();
                $this->showTagDropdown = true;
            } else {
                $this->showTagDropdown = false;
            }
        }
    }
    
    protected function searchTags()
    {
        $this->matchingTags = Tag::where('name', 'like', "%{$this->tagQuery}%")
            ->limit(5)
            ->pluck('name')
            ->toArray();
    }
    
    public function addTag($tag)
    {
        $currentTags = array_filter(array_map('trim', explode(',', $this->tags)));
        
        // Remove partial tag being typed
        if (!empty($currentTags)) {
            array_pop($currentTags);
        }
        
        $currentTags[] = $tag;
        $this->tags = implode(', ', $currentTags);
        $this->showTagDropdown = false;
    }
    
    // Content validation methods
    protected function validateContentFormat($text)
    {
        $this->contentWarnings = [];
        
        // Check for links and validate
        if ($this->detectLinks) {
            preg_match_all('/https?:\/\/\S+/', $text, $matches);
            foreach ($matches[0] as $url) {
                // Check if URL appears valid
                if (!filter_var($url, FILTER_VALIDATE_URL)) {
                    $this->contentWarnings[] = "URL appears to be invalid: {$url}";
                }
            }
        }
        
        // Check for repetitive content
        if (preg_match('/(.{15,})\1{2,}/i', $text)) {
            $this->contentWarnings[] = "Content appears to be repetitive";
        }
        
        // Suggest adding hashtags for long content
        if (strlen($text) > 200 && empty($this->tags)) {
            $this->contentWarnings[] = "Consider adding tags to help categorize your post";
        }
    }
    
    // Draft functionality methods
    public function autoSaveDraft()
    {
        if (empty($this->content) || $this->editingPostId) {
            return;
        }
        
        $draft = [
            'id' => $this->draftId ?? uniqid(),
            'content' => $this->content,
            'tags' => $this->tags,
            'pet_id' => $this->pet_id,
            'visibility' => $this->visibility,
            'updated_at' => now()->toDateTimeString()
        ];
        
        $this->draftId = $draft['id'];
        $this->draftMode = true;
        
        // Store in database or session
        auth()->user()->postDrafts()->updateOrCreate(
            ['id' => $draft['id']],
            $draft
        );
        
        $this->emit('draftSaved', $draft['id']);
    }
    
    protected function loadDraft()
    {
        if ($this->editingPostId) {
            return;
        }
        
        $latestDraft = auth()->user()->postDrafts()
            ->latest()
            ->first();
            
        if ($latestDraft) {
            $this->draftId = $latestDraft->id;
            $this->content = $latestDraft->content;
            $this->tags = $latestDraft->tags;
            $this->pet_id = $latestDraft->pet_id;
            $this->visibility = $latestDraft->visibility;
            $this->draftMode = true;
        }
    }
    
    protected function clearDraft()
    {
        if ($this->draftId) {
            auth()->user()->postDrafts()
                ->where('id', $this->draftId)
                ->delete();
            
            $this->draftId = null;
            $this->draftMode = false;
        }
    }
    
    // Post CRUD operations
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
        
        // Process and attach images
        if (count($this->images) > 0) {
            foreach ($this->images as $image) {
                $filename = uniqid() . '_' . $image->getClientOriginalName();
                $path = $image->storeAs('public/post-images', $filename);
                $post->images()->create([
                    'path' => str_replace('public/', '', $path),
                    'name' => $image->getClientOriginalName(),
                    'size' => $image->getSize(),
                    'mime_type' => $image->getMimeType(),
                ]);
            }
        }
        
        $this->attachTags($post);
        $this->handleMentions($post);
        $this->createActivityLog('post_created');
        $this->clearDraft();
        $this->resetFields();
        
        $this->emit('postCreated', $post->id);
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
        
        $this->emit('postUpdated', $post->id);
    }
    
    // Support methods
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
    
    protected function parseMentions($content)
    {
        preg_match_all('/@(\w+)/', $content, $matches);
        $mentionedUsers = User::whereIn('username', $matches[1])->get();
        return $mentionedUsers;
    }
    
    protected function attachTags($post)
    {
        if ($this->tags) {
            $tagNames = array_filter(array_map('trim', explode(',', $this->tags)));
            $post->tags()->detach(); // Remove existing tags
            
            foreach ($tagNames as $tagName) {
                $tag = Tag::firstOrCreate(['name' => strtolower($tagName)]);
                $post->tags()->attach($tag->id);
            }
        }
    }
    
    protected function createActivityLog($action)
    {
        $content = $action === 'post_created' ? $this->content : $this->editingContent;
        $description = $action === 'post_created' 
            ? "Created a post: " . substr($content, 0, 50) . (strlen($content) > 50 ? '...' : '')
            : "Updated a post: " . substr($content, 0, 50) . (strlen($content) > 50 ? '...' : '');
            
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
        $this->temporaryImages = [];
        $this->contentWarnings = [];
        $this->contentLength = 0;
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
            'pets' => auth()->user()->pets,
            'popularTags' => $this->popularTags,
        ]);
    }
}