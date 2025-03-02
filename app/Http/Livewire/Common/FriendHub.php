<?php

namespace App\Http\Livewire\Common;

use App\Traits\EntityTypeTrait;
use App\Traits\FriendshipTrait;
use App\Traits\ActivityTrait;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class FriendHub extends Component
{
    use EntityTypeTrait, FriendshipTrait, ActivityTrait;
    
    /**
     * Active tab
     *
     * @var string
     */
    public $activeTab = 'dashboard';
    
    /**
     * Whether to show follow functionality
     * Only applicable for user entities
     *
     * @var bool
     */
    public $showFollowFunctionality = false;
    
    /**
     * Statistics cache TTL in minutes
     *
     * @var int
     */
    protected $statsCacheTtl = 5;
    
    /**
     * Initialize the component
     *
     * @param string $entityType
     * @param int $entityId
     * @param string $activeTab
     * @param bool $showFollowFunctionality
     * @return void
     */
    public function mount(
        string $entityType,
        int $entityId,
        string $activeTab = 'dashboard',
        bool $showFollowFunctionality = false
    ) {
        $this->initializeEntity($entityType, $entityId);
        $this->activeTab = $activeTab;
        $this->showFollowFunctionality = $showFollowFunctionality && $entityType === 'user';
        
        // Check authorization
        if (!$this->isAuthorized()) {
            abort(403, 'You do not have permission to access this hub.');
        }
    }
    
    /**
     * Set the active tab
     *
     * @param string $tab
     * @return void
     */
    public function setActiveTab(string $tab)
    {
        $this->activeTab = $tab;
    }
    
    /**
     * Get friend statistics
     *
     * @return array
     */
    protected function getFriendStats()
    {
        $prefix = $this->entityType === 'pet' ? 'pet_' : 'user_';
        $cacheKey = "{$prefix}{$this->entityId}_friend_stats";
        
        return Cache::remember($cacheKey, now()->addMinutes($this->statsCacheTtl), function () {
            $entity = $this->getEntity();
            $friendIds = $this->getFriendIds();
            $friendCount = count($friendIds);
            
            $friendshipModel = $this->getFriendshipModel();
            $entityIdField = $this->getEntityIdField();
            $friendIdField = $this->getFriendIdField();
            
            // Get pending requests (received)
            $pendingReceivedCount = $friendshipModel::where($friendIdField, $this->entityId)
                ->where('status', 'pending')
                ->count();
                
            // Get pending requests (sent)
            $pendingSentCount = $friendshipModel::where($entityIdField, $this->entityId)
                ->where('status', 'pending')
                ->count();
                
            // Get recent friendships
            $recentFriendships = $friendshipModel::where(function($query) use ($entityIdField, $friendIdField) {
                $query->where($entityIdField, $this->entityId)
                      ->orWhere($friendIdField, $this->entityId);
            })
            ->where('status', 'accepted')
            ->orderBy('updated_at', 'desc')
            ->take(5)
            ->get();
            
            // Get friend IDs from recent friendships
            $recentFriendIds = [];
            foreach ($recentFriendships as $friendship) {
                if ($friendship->$entityIdField == $this->entityId) {
                    $recentFriendIds[] = $friendship->$friendIdField;
                } else {
                    $recentFriendIds[] = $friendship->$entityIdField;
                }
            }
            
            // Get recent friends
            $entityModel = $this->getEntityModel();
            $recentFriends = $entityModel::whereIn('id', $recentFriendIds)->get();
            
            // Map recent friends to include friendship date
            $recentFriendsWithDate = $recentFriends->map(function($friend) use ($recentFriendships, $entityIdField, $friendIdField) {
                foreach ($recentFriendships as $friendship) {
                    if (($friendship->$entityIdField == $this->entityId && $friendship->$friendIdField == $friend->id) ||
                        ($friendship->$friendIdField == $this->entityId && $friendship->$entityIdField == $friend->id)) {
                        $friend->friendship_date = $friendship->updated_at;
                        break;
                    }
                }
                return $friend;
            })->sortByDesc('friendship_date');
            
            // Get follow stats if applicable
            $followStats = null;
            if ($this->entityType === 'user' && $this->showFollowFunctionality) {
                $followStats = [
                    'following_count' => $entity->following()->count(),
                    'followers_count' => $entity->followers()->count(),
                    'recent_followers' => $entity->followers()->latest()->take(5)->get(),
                    'recent_following' => $entity->following()->latest()->take(5)->get(),
                ];
            }
            
            return [
                'friend_count' => $friendCount,
                'pending_received_count' => $pendingReceivedCount,
                'pending_sent_count' => $pendingSentCount,
                'recent_friends' => $recentFriendsWithDate,
                'follow_stats' => $followStats,
            ];
        });
    }
    
    /**
     * Get friend suggestions
     *
     * @param int $limit
     * @return \Illuminate\Support\Collection
     */
    protected function getFriendSuggestions(int $limit = 5)
    {
        $prefix = $this->entityType === 'pet' ? 'pet_' : 'user_';
        $cacheKey = "{$prefix}{$this->entityId}_friend_suggestions";
        
        return Cache::remember($cacheKey, now()->addMinutes($this->statsCacheTtl), function() use ($limit) {
            $friendIds = $this->getFriendIds();
            $entityModel = $this->getEntityModel();
            
            // Base query for suggestions
            $query = $entityModel::where('id', '!=', $this->entityId)
                ->whereNotIn('id', $friendIds);
                
            // Add entity-specific conditions
            if ($this->entityType === 'pet') {
                $query->where('user_id', '!=', auth()->id());
            } else {
                $query->where('id', '!=', auth()->id());
            }
            
            // Get suggestions
            $suggestions = $query->inRandomOrder()->limit($limit * 2)->get();
            
            // For each suggestion, calculate mutual friends
            foreach ($suggestions as $suggestion) {
                $mutualFriendIds = $this->getMutualFriendIds($suggestion->id);
                $suggestion->mutual_friend_count = count($mutualFriendIds);
                
                // Get mutual friend details if there are any
                if (!empty($mutualFriendIds)) {
                    $suggestion->mutual_friends = $entityModel::whereIn('id', array_slice($mutualFriendIds, 0, 3))->get();
                }
            }
            
            // Sort by mutual friend count (highest first)
            $suggestions = $suggestions->sortByDesc('mutual_friend_count');
            
            return $suggestions->take($limit);
        });
    }
    
    /**
     * Get recent activities
     *
     * @param int $limit
     * @return \Illuminate\Support\Collection
     */
    protected function getRecentActivities(int $limit = 5)
    {
        return $this->getRecentActivities($limit);
    }
    
    /**
     * Get friend activities
     *
     * @param int $limit
     * @return \Illuminate\Support\Collection
     */
    protected function getFriendActivities(int $limit = 5)
    {
        return $this->getFriendActivities($limit, null, 'week');
    }
    
    /**
     * Render the component
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        $stats = $this->getFriendStats();
        $suggestions = $this->getFriendSuggestions();
        $recentActivities = $this->getRecentActivities();
        $friendActivities = $this->getFriendActivities();
        
        return view('livewire.common.friend-hub', [
            'entityType' => $this->entityType,
            'entityId' => $this->entityId,
            'stats' => $stats,
            'suggestions' => $suggestions,
            'recentActivities' => $recentActivities,
            'friendActivities' => $friendActivities,
            'showFollowFunctionality' => $this->showFollowFunctionality,
        ]);
    }
}
