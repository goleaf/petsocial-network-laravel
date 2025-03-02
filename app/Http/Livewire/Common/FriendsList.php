<?php

namespace App\Http\Livewire\Common;

use App\Traits\EntityTypeTrait;
use App\Traits\FriendshipTrait;
use App\Traits\ActivityTrait;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class FriendsList extends Component
{
    use EntityTypeTrait, FriendshipTrait, ActivityTrait, WithPagination;
    
    /**
     * The current filter for the friends list
     *
     * @var string
     */
    public $filter = 'all';
    
    /**
     * The current search query
     *
     * @var string
     */
    public $search = '';
    
    /**
     * The current sort field
     *
     * @var string
     */
    public $sortField = 'name';
    
    /**
     * The current sort direction
     *
     * @var string
     */
    public $sortDirection = 'asc';
    
    /**
     * The number of items to show per page
     *
     * @var int
     */
    public $perPage = 10;
    
    /**
     * Whether to show the filter controls
     *
     * @var bool
     */
    public $showFilters = true;
    
    /**
     * Whether to show the search box
     *
     * @var bool
     */
    public $showSearch = true;
    
    /**
     * Whether to show pagination controls
     *
     * @var bool
     */
    public $showPagination = true;
    
    /**
     * Whether to show the friend button
     *
     * @var bool
     */
    public $showFriendButton = true;
    
    /**
     * Initialize the component
     *
     * @param string $entityType
     * @param int $entityId
     * @param string $filter
     * @param bool $showFilters
     * @param bool $showSearch
     * @param bool $showPagination
     * @param bool $showFriendButton
     * @param int $perPage
     * @return void
     */
    public function mount(
        string $entityType,
        int $entityId,
        string $filter = 'all',
        bool $showFilters = true,
        bool $showSearch = true,
        bool $showPagination = true,
        bool $showFriendButton = true,
        int $perPage = 10
    ) {
        $this->initializeEntity($entityType, $entityId);
        $this->filter = $filter;
        $this->showFilters = $showFilters;
        $this->showSearch = $showSearch;
        $this->showPagination = $showPagination;
        $this->showFriendButton = $showFriendButton;
        $this->perPage = $perPage;
        
        // Check authorization
        if (!$this->isAuthorized()) {
            abort(403, 'You do not have permission to view this friends list.');
        }
    }
    
    /**
     * Set the filter
     *
     * @param string $filter
     * @return void
     */
    public function setFilter(string $filter)
    {
        $this->resetPage();
        $this->filter = $filter;
        $this->clearFriendsListCache();
    }
    
    /**
     * Update the search query
     *
     * @return void
     */
    public function updatedSearch()
    {
        $this->resetPage();
        $this->clearFriendsListCache();
    }
    
    /**
     * Sort by the given field
     *
     * @param string $field
     * @return void
     */
    public function sortBy(string $field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
        
        $this->clearFriendsListCache();
    }
    
    /**
     * Get the friends based on the current filter
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    protected function getFriends()
    {
        // Create a cache key based on all current filters and pagination state
        $cacheKey = "{$this->entityType}_{$this->entityId}_friends_{$this->filter}_{$this->search}_{$this->sortField}_{$this->sortDirection}_page{$this->page}_perPage{$this->perPage}";
        
        return \Illuminate\Support\Facades\Cache::remember($cacheKey, now()->addMinutes(5), function() {
            $entityModel = $this->getEntityModel();
            $query = null;
            
            switch ($this->filter) {
                case 'pending':
                    $query = $this->getPendingFriendRequests();
                    break;
                case 'sent':
                    $query = $this->getSentFriendRequests();
                    break;
                case 'mutual':
                    $query = $this->getMutualFriends();
                    break;
                case 'recent':
                    $query = $this->getRecentFriends();
                    break;
                case 'all':
                default:
                    $query = $this->getAllFriends();
                    break;
            }
            
            // Apply search if provided
            if (!empty($this->search)) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%');
                      
                    if ($this->entityType === 'pet') {
                        $q->orWhere('type', 'like', '%' . $this->search . '%')
                          ->orWhere('breed', 'like', '%' . $this->search . '%');
                    }
                });
            }
            
            // Apply sorting
            $query->orderBy($this->sortField, $this->sortDirection);
            
            // Use eager loading for better performance
            if ($this->entityType === 'pet') {
                $query->with(['user', 'species', 'breed']);
            } else {
                $query->with(['profile']);
            }
            
            return $query->paginate($this->perPage);
        });
    }
    
    /**
     * Get all friends
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function getAllFriends()
    {
        $friendIds = $this->getCachedFriendIds();
        $entityModel = $this->getEntityModel();
        
        return $entityModel::whereIn('id', $friendIds);
    }
    
    /**
     * Get cached friend IDs
     *
     * @return array
     */
    protected function getCachedFriendIds()
    {
        $cacheKey = "{$this->entityType}_{$this->entityId}_friend_ids";
        
        return \Illuminate\Support\Facades\Cache::remember($cacheKey, now()->addHours(1), function() {
            return $this->getFriendIds();
        });
    }
    
    /**
     * Get pending friend requests
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function getPendingFriendRequests()
    {
        $pendingIds = $this->getCachedPendingFriendRequestIds();
        $entityModel = $this->getEntityModel();
        
        return $entityModel::whereIn('id', $pendingIds);
    }
    
    /**
     * Get cached pending friend request IDs
     *
     * @return array
     */
    protected function getCachedPendingFriendRequestIds()
    {
        $cacheKey = "{$this->entityType}_{$this->entityId}_pending_friend_request_ids";
        
        return \Illuminate\Support\Facades\Cache::remember($cacheKey, now()->addMinutes(15), function() {
            return $this->getPendingFriendRequestIds();
        });
    }
    
    /**
     * Get sent friend requests
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function getSentFriendRequests()
    {
        $sentIds = $this->getCachedSentFriendRequestIds();
        $entityModel = $this->getEntityModel();
        
        return $entityModel::whereIn('id', $sentIds);
    }
    
    /**
     * Get cached sent friend request IDs
     *
     * @return array
     */
    protected function getCachedSentFriendRequestIds()
    {
        $cacheKey = "{$this->entityType}_{$this->entityId}_sent_friend_request_ids";
        
        return \Illuminate\Support\Facades\Cache::remember($cacheKey, now()->addMinutes(15), function() {
            return $this->getSentFriendRequestIds();
        });
    }
    
    /**
     * Get mutual friends
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function getMutualFriends()
    {
        // This requires a target entity ID to compare mutual friends with
        // For now, we'll just return all friends
        return $this->getAllFriends();
    }
    
    /**
     * Get recent friends
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function getRecentFriends()
    {
        $friendIds = $this->getCachedRecentFriendIds();
        $entityModel = $this->getEntityModel();
        
        return $entityModel::whereIn('id', $friendIds);
    }
    
    /**
     * Get cached recent friend IDs
     *
     * @return array
     */
    protected function getCachedRecentFriendIds()
    {
        $cacheKey = "{$this->entityType}_{$this->entityId}_recent_friend_ids";
        
        return \Illuminate\Support\Facades\Cache::remember($cacheKey, now()->addMinutes(30), function() {
            return $this->getRecentFriendIds();
        });
    }
    
    /**
     * Clear friends list related caches
     *
     * @return void
     */
    protected function clearFriendsListCache()
    {
        // Clear pattern-based caches for this entity
        $cachePattern = "{$this->entityType}_{$this->entityId}_friends_*";
        $this->clearCacheByPattern($cachePattern);
        
        // Clear specific caches
        \Illuminate\Support\Facades\Cache::forget("{$this->entityType}_{$this->entityId}_friend_ids");
        \Illuminate\Support\Facades\Cache::forget("{$this->entityType}_{$this->entityId}_pending_friend_request_ids");
        \Illuminate\Support\Facades\Cache::forget("{$this->entityType}_{$this->entityId}_sent_friend_request_ids");
        \Illuminate\Support\Facades\Cache::forget("{$this->entityType}_{$this->entityId}_recent_friend_ids");
    }
    
    /**
     * Clear cache by pattern (using cache tags if available, otherwise clearing specific keys)
     *
     * @param string $pattern
     * @return void
     */
    protected function clearCacheByPattern($pattern)
    {
        // In a real implementation, you would use cache tags if available
        // For simplicity, we'll just clear specific commonly used cache keys
        $filters = ['all', 'pending', 'sent', 'mutual', 'recent'];
        $perPages = [10, 20, 50];
        
        foreach ($filters as $filter) {
            foreach ($perPages as $perPage) {
                for ($page = 1; $page <= 5; $page++) { // Clear first 5 pages of each type
                    $key = "{$this->entityType}_{$this->entityId}_friends_{$filter}__name_asc_page{$page}_perPage{$perPage}";
                    \Illuminate\Support\Facades\Cache::forget($key);
                    
                    // Also clear with common search terms if needed
                    // This is a simplified approach - in production you might use Redis or a more sophisticated cache clearing
                }
            }
        }
    }

    /**
     * Render the component
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        $friends = $this->getFriends();
        
        return view('livewire.common.friends-list', [
            'friends' => $friends,
            'entityType' => $this->entityType,
            'entityId' => $this->entityId,
        ]);
    }
}
