<?php

namespace App\Http\Livewire\Common\Friend;

use App\Models\User;
use App\Models\Pet;
use App\Models\Friendship;
use App\Models\PetFriendship;
use App\Models\Follow;
use App\Traits\EntityTypeTrait;
use App\Traits\FriendshipTrait;
use Livewire\Component;
use Illuminate\Support\Facades\Cache;

class Hub extends Component
{
    use EntityTypeTrait, FriendshipTrait;
    
    public $activeTab = 'overview';
    public $stats = [];
    
    protected $listeners = [
        'refresh' => '$refresh',
        'friendRequestSent' => 'handleFriendRequestSent',
        'friendRequestAccepted' => 'handleFriendRequestAccepted',
        'friendRequestDeclined' => 'handleFriendRequestDeclined',
        'friendRequestCancelled' => 'handleFriendRequestCancelled',
        'friendRemoved' => 'handleFriendRemoved',
        'friendCategorized' => 'handleFriendCategorized',
    ];

    public function mount($entityType = 'user', $entityId = null)
    {
        $this->entityType = $entityType;
        $this->entityId = $entityId ?? ($entityType === 'user' ? auth()->id() : null);
        
        if (!$this->entityId) {
            throw new \InvalidArgumentException("Entity ID is required");
        }
        
        $this->loadStats();
    }
    
    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
    }
    
    public function loadStats()
    {
        $entity = $this->getEntity();
        $prefix = $this->entityType === 'pet' ? 'pet_' : 'user_';
        $cacheKey = "{$prefix}{$this->entityId}_friend_stats";
        
        $this->stats = Cache::remember($cacheKey, now()->addHours(1), function() use ($entity) {
            $friendshipModel = $this->getFriendshipModel();
            $entityIdField = $this->getEntityIdField();
            $friendIdField = $this->getFriendIdField();
            
            $stats = [
                'total_friends' => count($this->getFriendIds()),
                'pending_sent' => 0,
                'pending_received' => 0,
                'recent_activity' => 0,
                'categories' => [],
            ];
            
            // Count pending sent requests
            $stats['pending_sent'] = $friendshipModel::where($entityIdField, $this->entityId)
                ->where('status', 'pending')
                ->count();
                
            // Count pending received requests
            $stats['pending_received'] = $friendshipModel::where($friendIdField, $this->entityId)
                ->where('status', 'pending')
                ->count();
                
            // Get category distribution
            $categoryStats = $friendshipModel::where($entityIdField, $this->entityId)
                ->whereNotNull('category')
                ->selectRaw('category, count(*) as count')
                ->groupBy('category')
                ->pluck('count', 'category')
                ->toArray();
                
            $stats['categories'] = $categoryStats;
            
            // For users, also get follower/following stats
            if ($this->entityType === 'user') {
                $stats['followers'] = Follow::where('followed_id', $this->entityId)->count();
                $stats['following'] = Follow::where('follower_id', $this->entityId)->count();
            }
            
            return $stats;
        });
    }
    
    public function handleFriendRequestSent()
    {
        $this->clearFriendCache();
        $this->loadStats();
    }
    
    public function handleFriendRequestAccepted()
    {
        $this->clearFriendCache();
        $this->loadStats();
    }
    
    public function handleFriendRequestDeclined()
    {
        $this->clearFriendCache();
        $this->loadStats();
    }
    
    public function handleFriendRequestCancelled()
    {
        $this->clearFriendCache();
        $this->loadStats();
    }
    
    public function handleFriendRemoved()
    {
        $this->clearFriendCache();
        $this->loadStats();
    }
    
    public function handleFriendCategorized()
    {
        $this->clearFriendCache();
        $this->loadStats();
    }
    
    public function clearFriendCache()
    {
        $prefix = $this->entityType === 'pet' ? 'pet_' : 'user_';
        Cache::forget("{$prefix}{$this->entityId}_friend_stats");
        $this->clearEntityCache();
    }
    
    public function render()
    {
        return view('livewire.common.friend.hub', [
            'entity' => $this->getEntity()
        ]);
    }
}
