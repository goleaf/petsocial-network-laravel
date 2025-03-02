<?php

namespace App\Http\Livewire\Common;

use App\Models\Post;
use App\Models\User;
use App\Models\Pet;
use App\Models\Tag;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;
use Livewire\WithPagination;

class UnifiedSearch extends Component
{
    use WithPagination;

    public $query = '';
    public $type = 'all'; // all, posts, users, pets, tags
    public $filter = 'all'; // all, friends, following
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $perPage = 10;
    
    protected $queryString = [
        'query' => ['except' => ''],
        'type' => ['except' => 'all'],
        'filter' => ['except' => 'all'],
        'sortField' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
    ];
    
    protected $paginationTheme = 'tailwind';
    
    protected $listeners = [
        'refreshSearch' => '$refresh',
    ];
    
    public function mount($initialQuery = '', $initialType = 'all')
    {
        $this->query = $initialQuery;
        $this->type = $initialType;
    }
    
    public function updatedQuery()
    {
        $this->resetPage();
        $this->clearSearchCache();
    }
    
    public function updatedType()
    {
        $this->resetPage();
        $this->clearSearchCache();
    }
    
    public function updatedFilter()
    {
        $this->resetPage();
        $this->clearSearchCache();
    }
    
    public function updatedSortField()
    {
        $this->resetPage();
        $this->clearSearchCache();
    }
    
    public function updatedSortDirection()
    {
        $this->resetPage();
        $this->clearSearchCache();
    }
    
    protected function clearSearchCache()
    {
        $types = ['all', 'posts', 'users', 'pets', 'tags'];
        $filters = ['all', 'friends', 'following'];
        $sortFields = ['created_at', 'name', 'popularity'];
        $sortDirections = ['asc', 'desc'];
        
        // Clear cache for common combinations
        foreach ($types as $type) {
            foreach ($filters as $filter) {
                foreach ($sortFields as $field) {
                    foreach ($sortDirections as $direction) {
                        $cacheKey = "search_{$type}_{$filter}_{$field}_{$direction}_" . md5($this->query);
                        Cache::forget($cacheKey);
                    }
                }
            }
        }
    }
    
    protected function getSearchResults()
    {
        // If query is empty and type is all, return empty results
        if (empty($this->query) && $this->type === 'all') {
            return [
                'posts' => collect(),
                'users' => collect(),
                'pets' => collect(),
                'tags' => collect(),
                'total' => 0,
            ];
        }
        
        // Generate cache key based on search parameters
        $cacheKey = "search_{$this->type}_{$this->filter}_{$this->sortField}_{$this->sortDirection}_" . md5($this->query);
        
        // Cache results for 5 minutes
        return Cache::remember($cacheKey, now()->addMinutes(5), function () {
            $results = [];
            $total = 0;
            
            // Get blocked user IDs
            $blockedIds = auth()->user()->blocks ? auth()->user()->blocks->pluck('id')->toArray() : [];
            
            // Get friend IDs for filtering using the helper method
            $friendIds = auth()->user()->getFriendIds();
            
            // Get following IDs for filtering - simplified to avoid DB issues
            $followingIds = [];
            
            // Search posts if type is 'all' or 'posts'
            if ($this->type === 'all' || $this->type === 'posts') {
                $postsQuery = Post::query()
                    ->where(function ($query) {
                        $query->where('content', 'like', '%' . $this->query . '%')
                            ->orWhereHas('tags', function ($q) {
                                $q->where('name', 'like', '%' . $this->query . '%');
                            });
                    })
                    ->whereNotIn('user_id', $blockedIds);
                
                // Apply visibility filters
                $postsQuery->where(function ($query) use ($friendIds) {
                    $query->where('posts_visibility', 'public')
                        ->orWhere(function ($q) use ($friendIds) {
                            $q->where('posts_visibility', 'friends')
                                ->whereIn('user_id', $friendIds);
                        })
                        ->orWhere('user_id', auth()->id());
                });
                
                // Apply friend/following filter
                if ($this->filter === 'friends') {
                    $postsQuery->whereIn('user_id', $friendIds);
                } elseif ($this->filter === 'following') {
                    $postsQuery->whereIn('user_id', $followingIds);
                }
                
                // Apply sorting
                if ($this->sortField === 'popularity') {
                    $postsQuery->withCount('reactions')->orderBy('reactions_count', $this->sortDirection);
                } else {
                    $postsQuery->orderBy($this->sortField, $this->sortDirection);
                }
                
                // Eager load relationships
                $postsQuery->with(['user', 'pet', 'tags', 'reactions']);
                
                // Get paginated results
                $posts = $postsQuery->paginate($this->perPage);
                $results['posts'] = $posts;
                $total += $posts->total();
            } else {
                $results['posts'] = collect();
            }
            
            // Search users if type is 'all' or 'users'
            if ($this->type === 'all' || $this->type === 'users') {
                $usersQuery = User::query()
                    ->where(function ($query) {
                        $query->where('name', 'like', '%' . $this->query . '%')
                            ->orWhere('email', 'like', '%' . $this->query . '%')
                            ->orWhere('bio', 'like', '%' . $this->query . '%');
                    })
                    ->whereNotIn('id', $blockedIds)
                    ->where('id', '!=', auth()->id());
                
                // Apply visibility filters
                $usersQuery->where(function ($query) use ($friendIds) {
                    $query->where('profile_visibility', 'public')
                        ->orWhere(function ($q) use ($friendIds) {
                            $q->where('profile_visibility', 'friends')
                                ->whereIn('id', $friendIds);
                        });
                });
                
                // Apply friend/following filter
                if ($this->filter === 'friends') {
                    $usersQuery->whereIn('id', $friendIds);
                } elseif ($this->filter === 'following') {
                    $usersQuery->whereIn('id', $followingIds);
                }
                
                // Apply sorting
                if ($this->sortField === 'popularity') {
                    $usersQuery->withCount('followers')->orderBy('followers_count', $this->sortDirection);
                } else {
                    $usersQuery->orderBy($this->sortField, $this->sortDirection);
                }
                
                // Eager load relationships
                $usersQuery->with(['posts' => function ($q) {
                    $q->latest()->limit(3);
                }, 'followers', 'following']);
                
                // Get paginated results
                $users = $usersQuery->paginate($this->perPage);
                $results['users'] = $users;
                $total += $users->total();
            } else {
                $results['users'] = collect();
            }
            
            // Search pets if type is 'all' or 'pets'
            if ($this->type === 'all' || $this->type === 'pets') {
                $petsQuery = Pet::query()
                    ->where(function ($query) {
                        $query->where('name', 'like', '%' . $this->query . '%')
                            ->orWhere('type', 'like', '%' . $this->query . '%')
                            ->orWhere('breed', 'like', '%' . $this->query . '%')
                            ->orWhere('bio', 'like', '%' . $this->query . '%');
                    })
                    ->whereNotIn('user_id', $blockedIds);
                
                // Apply visibility filters
                $petsQuery->where(function ($query) use ($friendIds) {
                    $query->where('visibility', 'public')
                        ->orWhere(function ($q) use ($friendIds) {
                            $q->where('visibility', 'friends')
                                ->whereIn('user_id', $friendIds);
                        })
                        ->orWhere('user_id', auth()->id());
                });
                
                // Apply friend/following filter
                if ($this->filter === 'friends') {
                    $petsQuery->whereIn('user_id', $friendIds);
                } elseif ($this->filter === 'following') {
                    $petsQuery->whereIn('user_id', $followingIds);
                }
                
                // Apply sorting
                if ($this->sortField === 'popularity') {
                    $petsQuery->withCount('followers')->orderBy('followers_count', $this->sortDirection);
                } else {
                    $petsQuery->orderBy($this->sortField, $this->sortDirection);
                }
                
                // Eager load relationships
                $petsQuery->with(['user', 'posts' => function ($q) {
                    $q->latest()->limit(3);
                }]);
                
                // Get paginated results
                $pets = $petsQuery->paginate($this->perPage);
                $results['pets'] = $pets;
                $total += $pets->total();
            } else {
                $results['pets'] = collect();
            }
            
            // Search tags if type is 'all' or 'tags'
            if ($this->type === 'all' || $this->type === 'tags') {
                $tagsQuery = Tag::query()
                    ->where('name', 'like', '%' . $this->query . '%');
                
                // Apply sorting
                if ($this->sortField === 'popularity') {
                    $tagsQuery->withCount('posts')->orderBy('posts_count', $this->sortDirection);
                } else {
                    $tagsQuery->orderBy($this->sortField, $this->sortDirection);
                }
                
                // Eager load relationships
                $tagsQuery->with(['posts' => function ($q) use ($blockedIds, $friendIds) {
                    $q->whereNotIn('user_id', $blockedIds)
                        ->where(function ($query) use ($friendIds) {
                            $query->where('posts_visibility', 'public')
                                ->orWhere(function ($q) use ($friendIds) {
                                    $q->where('posts_visibility', 'friends')
                                        ->whereIn('user_id', $friendIds);
                                })
                                ->orWhere('user_id', auth()->id());
                        })
                        ->latest()
                        ->limit(5);
                }]);
                
                // Get paginated results
                $tags = $tagsQuery->paginate($this->perPage);
                $results['tags'] = $tags;
                $total += $tags->total();
            } else {
                $results['tags'] = collect();
            }
            
            $results['total'] = $total;
            return $results;
        });
    }
    
    public function render()
    {
        return view('livewire.common.unified-search', [
            'results' => $this->getSearchResults(),
        ])->layout('layouts.app');
    }
}
