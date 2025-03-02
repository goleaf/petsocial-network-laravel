<?php

namespace App\Http\Livewire\Common;

use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use App\Models\Pet;
use App\Notifications\ActivityNotification;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class PostManager extends Component
{
    use WithPagination, WithFileUploads;

    public $content;
    public $tags = '';
    public $pet_id;
    public $entityType = 'user';
    public $entityId;
    public $entity;
    public $editingPostId;
    public $editingContent;
    public $editingTags;
    public $photo;
    public $filter = 'all'; // all, user, friends, pets
    public $searchTerm = '';
    
    protected $paginationTheme = 'tailwind';
    
    protected $listeners = [
        'postCreated' => '$refresh',
        'postUpdated' => '$refresh',
        'postDeleted' => '$refresh',
        'refreshPosts' => '$refresh',
    ];
    
    protected $rules = [
        'content' => 'required|max:500',
        'tags' => 'nullable|string',
        'pet_id' => 'nullable|exists:pets,id',
        'photo' => 'nullable|image|max:5120', // 5MB max
    ];

    public function mount($entityType = 'user', $entityId = null)
    {
        $this->entityType = $entityType;
        $this->entityId = $entityId ?? auth()->id();
        
        // Load the entity
        if ($entityType === 'user') {
            $this->entity = User::findOrFail($this->entityId);
            
            // Check authorization for viewing user posts
            if ($this->entity->id !== auth()->id() && 
                ($this->entity->profile_visibility === 'private' || 
                ($this->entity->profile_visibility === 'friends' && !$this->entity->friends->contains(auth()->id())))) {
                abort(403, 'You do not have permission to view these posts.');
            }
        } else if ($entityType === 'pet') {
            $this->entity = Pet::findOrFail($this->entityId);
            
            // Check if the authenticated user owns this pet or if the pet's profile is public
            if ($this->entity->user_id !== auth()->id() && $this->entity->visibility === 'private') {
                abort(403, 'You do not have permission to view this pet\'s posts.');
            }
        } else {
            abort(400, 'Invalid entity type.');
        }
    }
    
    public function updatedFilter()
    {
        $this->resetPage();
        // Clear any cached posts for this filter
        $this->clearPostsCache();
    }
    
    public function updatedSearchTerm()
    {
        $this->resetPage();
        // Clear any cached posts for this search
        $this->clearPostsCache();
    }
    
    protected function clearPostsCache()
    {
        // Clear cache for different filter combinations
        $cacheKeys = [
            "{$this->entityType}_{$this->entityId}_posts_all",
            "{$this->entityType}_{$this->entityId}_posts_user",
            "{$this->entityType}_{$this->entityId}_posts_friends",
            "{$this->entityType}_{$this->entityId}_posts_pets",
        ];
        
        foreach ($cacheKeys as $key) {
            Cache::forget($key);
        }
    }

    public function save()
    {
        $this->validate();
        
        $postData = [
            'user_id' => auth()->id(),
            'content' => $this->content,
            'pet_id' => $this->pet_id,
        ];
        
        $post = Post::create($postData);
        
        // Handle photo upload if present
        if ($this->photo) {
            $filename = $this->photo->store('post-photos', 'public');
            $post->update(['photo' => $filename]);
        }
        
        $this->attachTags($post);
        
        // Process mentions and send notifications
        $mentionedUsers = $this->parseMentions($this->content);
        foreach ($mentionedUsers as $user) {
            if ($user->id !== auth()->id()) {
                $user->notify(new ActivityNotification('mention', auth()->user(), $post));
            }
        }
        
        // Log activity
        auth()->user()->activityLogs()->create([
            'action' => 'post_created',
            'description' => "Created a post: " . substr($this->content, 0, 50) . (strlen($this->content) > 50 ? '...' : ''),
        ]);
        
        // Clear post cache
        $this->clearPostsCache();
        
        // Reset form
        $this->reset(['content', 'tags', 'pet_id', 'photo']);
        
        // Emit event for other components
        $this->emit('postCreated');
        
        session()->flash('message', 'Post created successfully!');
    }

    public function edit($postId)
    {
        $post = Post::where('user_id', auth()->id())->with('tags')->find($postId);
        
        if ($post) {
            $this->editingPostId = $postId;
            $this->editingContent = $post->content;
            $this->editingTags = $post->tags->pluck('name')->implode(', ');
        }
    }

    public function update()
    {
        $this->validate([
            'editingContent' => 'required|max:500',
            'editingTags' => 'nullable|string',
        ]);
        
        $post = Post::where('user_id', auth()->id())->find($this->editingPostId);
        
        if ($post) {
            $post->update(['content' => $this->editingContent]);
            
            // Update tags
            $post->tags()->detach();
            
            if ($this->editingTags) {
                $tagNames = array_filter(array_map('trim', explode(',', $this->editingTags)));
                foreach ($tagNames as $tagName) {
                    $tag = Tag::firstOrCreate(['name' => strtolower($tagName)]);
                    $post->tags()->attach($tag->id);
                }
            }
            
            // Clear post cache
            $this->clearPostsCache();
            
            // Reset form
            $this->reset(['editingPostId', 'editingContent', 'editingTags']);
            
            // Emit event for other components
            $this->emit('postUpdated');
            
            session()->flash('message', 'Post updated successfully!');
        }
    }
    
    public function delete($postId)
    {
        $post = Post::where('user_id', auth()->id())->find($postId);
        
        if ($post) {
            $post->delete();
            
            // Clear post cache
            $this->clearPostsCache();
            
            // Emit event for other components
            $this->emit('postDeleted');
            
            session()->flash('message', 'Post deleted successfully!');
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
        return User::whereIn('name', $matches[1])->get();
    }
    
    protected function getPosts()
    {
        $cacheKey = "{$this->entityType}_{$this->entityId}_posts_{$this->filter}";
        
        if (!empty($this->searchTerm)) {
            // Don't cache search results
            return $this->getPostsQuery()->paginate(10);
        }
        
        return Cache::remember($cacheKey, now()->addMinutes(5), function () {
            return $this->getPostsQuery()->paginate(10);
        });
    }
    
    protected function getPostsQuery()
    {
        $query = Post::query();
        
        // Apply entity filter
        if ($this->entityType === 'user') {
            if ($this->filter === 'user') {
                // Only user's posts
                $query->where('user_id', $this->entityId)->whereNull('pet_id');
            } elseif ($this->filter === 'pets') {
                // Only posts from user's pets
                $petIds = Pet::where('user_id', $this->entityId)->pluck('id');
                $query->whereIn('pet_id', $petIds);
            } elseif ($this->filter === 'friends') {
                // Posts from user's friends
                $friendIds = $this->entity->friends()->pluck('users.id');
                $query->whereIn('user_id', $friendIds);
            } else {
                // All posts (user + user's pets + friends if viewing own profile)
                if ($this->entityId === auth()->id()) {
                    $friendIds = $this->entity->friends()->pluck('users.id');
                    $petIds = Pet::where('user_id', $this->entityId)->pluck('id');
                    
                    $query->where(function ($q) use ($friendIds, $petIds) {
                        $q->where('user_id', $this->entityId)
                          ->orWhereIn('user_id', $friendIds)
                          ->orWhereIn('pet_id', $petIds);
                    });
                } else {
                    // Just the user's posts and their pets' posts
                    $petIds = Pet::where('user_id', $this->entityId)->pluck('id');
                    
                    $query->where(function ($q) use ($petIds) {
                        $q->where('user_id', $this->entityId)
                          ->orWhereIn('pet_id', $petIds);
                    });
                }
            }
        } elseif ($this->entityType === 'pet') {
            // Only posts from this pet
            $query->where('pet_id', $this->entityId);
        }
        
        // Apply search filter if provided
        if (!empty($this->searchTerm)) {
            $query->where('content', 'like', '%' . $this->searchTerm . '%');
        }
        
        // Eager load relationships
        $query->with(['user', 'pet', 'tags', 'likes', 'comments' => function ($q) {
            $q->latest()->limit(3)->with('user');
        }]);
        
        // Order by latest
        $query->latest();
        
        return $query;
    }

    public function render()
    {
        return view('livewire.common.post-manager', [
            'posts' => $this->getPosts(),
            'userPets' => Pet::where('user_id', auth()->id())->get(),
        ]);
    }
}
