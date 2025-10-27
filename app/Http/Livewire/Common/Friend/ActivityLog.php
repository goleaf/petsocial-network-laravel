<?php

namespace App\Http\Livewire\Common\Friend;

use App\Models\User;
use App\Models\Pet;
use App\Models\PetActivity;
use App\Models\UserActivity;
use App\Traits\EntityTypeTrait;
use App\Traits\ActivityTrait;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class ActivityLog extends Component
{
    use WithPagination, EntityTypeTrait, ActivityTrait;
    
    public $typeFilter = null;
    public $dateFilter = null;
    public $showFriendActivities = true;
    public $page = 1;
    public $perPage = 10;
    
    protected $queryString = [
        'typeFilter' => ['except' => ''],
        'dateFilter' => ['except' => ''],
        'page' => ['except' => 1],
    ];
    
    protected $listeners = [
        'refresh' => '$refresh',
        'activityCreated' => 'handleActivityCreated',
        'activityUpdated' => 'handleActivityUpdated',
        'activityDeleted' => 'handleActivityDeleted',
    ];

    public function mount($entityType = 'user', $entityId = null)
    {
        $this->entityType = $entityType;
        $this->entityId = $entityId ?? ($entityType === 'user' ? auth()->id() : null);

        if (!$this->entityId) {
            throw new \InvalidArgumentException(__('friends.entity_id_required'));
        }

        if ($this->entityType === 'user') {
            $targetUser = User::findOrFail($this->entityId);

            if (!$targetUser->canViewPrivacySection(auth()->user(), 'activity') && !auth()->user()->isAdmin()) {
                abort(403, __('profile.activity_private'));
            }
        }
    }
    
    public function updatingTypeFilter()
    {
        $this->resetPage();
    }
    
    public function updatingDateFilter()
    {
        $this->resetPage();
    }
    
    public function updatingShowFriendActivities()
    {
        $this->resetPage();
    }
    
    public function handleActivityCreated()
    {
        $this->clearActivityCache();
    }
    
    public function handleActivityUpdated()
    {
        $this->clearActivityCache();
    }
    
    public function handleActivityDeleted()
    {
        $this->clearActivityCache();
    }
    
    public function clearActivityCache()
    {
        $prefix = $this->entityType === 'pet' ? 'pet_' : 'user_';
        Cache::forget("{$prefix}{$this->entityId}_activities_{$this->typeFilter}_{$this->dateFilter}_page{$this->page}");
        Cache::forget("{$prefix}{$this->entityId}_friend_activities");
        Cache::forget("{$prefix}{$this->entityId}_activity_stats");
    }
    
    public function getRecentActivities(int $limit = 10, ?string $filter = null): Collection
    {
        $entity = $this->getEntity();
        $prefix = $this->entityType === 'pet' ? 'pet_' : 'user_';
        $cacheKey = "{$prefix}{$this->entityId}_activities_{$filter}_recent{$limit}";
        
        return Cache::remember($cacheKey, now()->addMinutes(5), function() use ($entity, $limit, $filter) {
            if ($this->entityType === 'pet') {
                $query = PetActivity::where('pet_id', $this->entityId);
            } else {
                $query = UserActivity::where('user_id', $this->entityId);
            }
            
            if ($filter) {
                $query->where('activity_type', $filter);
            }
            
            return $query->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();
        });
    }
    
    public function getFriendActivities(): Collection
    {
        $entity = $this->getEntity();
        $prefix = $this->entityType === 'pet' ? 'pet_' : 'user_';
        $cacheKey = "{$prefix}{$this->entityId}_friend_activities";
        
        return Cache::remember($cacheKey, now()->addMinutes(5), function() use ($entity) {
            $friendIds = $this->getFriendIds();
            
            if (empty($friendIds)) {
                return collect();
            }
            
            if ($this->entityType === 'pet') {
                return PetActivity::whereIn('pet_id', $friendIds)
                    ->orderBy('created_at', 'desc')
                    ->limit(20)
                    ->get();
            } else {
                return UserActivity::whereIn('user_id', $friendIds)
                    ->orderBy('created_at', 'desc')
                    ->limit(20)
                    ->get();
            }
        });
    }
    
    public function getActivityStatistics(): array
    {
        $entity = $this->getEntity();
        $prefix = $this->entityType === 'pet' ? 'pet_' : 'user_';
        $cacheKey = "{$prefix}{$this->entityId}_activity_stats";
        
        return Cache::remember($cacheKey, now()->addHours(1), function() use ($entity) {
            if ($this->entityType === 'pet') {
                $totalCount = PetActivity::where('pet_id', $this->entityId)->count();
                
                $typeCounts = PetActivity::where('pet_id', $this->entityId)
                    ->select('activity_type', \DB::raw('count(*) as count'))
                    ->groupBy('activity_type')
                    ->pluck('count', 'activity_type')
                    ->toArray();
                    
                $monthlyActivity = PetActivity::where('pet_id', $this->entityId)
                    ->where('created_at', '>=', now()->subMonths(6))
                    ->select(
                        \DB::raw('YEAR(created_at) as year'),
                        \DB::raw('MONTH(created_at) as month'),
                        \DB::raw('count(*) as count')
                    )
                    ->groupBy('year', 'month')
                    ->get()
                    ->keyBy(function ($item) {
                        return $item->year . '-' . str_pad($item->month, 2, '0', STR_PAD_LEFT);
                    })
                    ->toArray();
            } else {
                $totalCount = UserActivity::where('user_id', $this->entityId)->count();
                
                $typeCounts = UserActivity::where('user_id', $this->entityId)
                    ->select('activity_type', \DB::raw('count(*) as count'))
                    ->groupBy('activity_type')
                    ->pluck('count', 'activity_type')
                    ->toArray();
                    
                $monthlyActivity = UserActivity::where('user_id', $this->entityId)
                    ->where('created_at', '>=', now()->subMonths(6))
                    ->select(
                        \DB::raw('YEAR(created_at) as year'),
                        \DB::raw('MONTH(created_at) as month'),
                        \DB::raw('count(*) as count')
                    )
                    ->groupBy('year', 'month')
                    ->get()
                    ->keyBy(function ($item) {
                        return $item->year . '-' . str_pad($item->month, 2, '0', STR_PAD_LEFT);
                    })
                    ->toArray();
            }
            
            return [
                'total' => $totalCount,
                'by_type' => $typeCounts,
                'monthly' => $monthlyActivity
            ];
        });
    }
    
    public function render()
    {
        // Generate cache key based on current filters and pagination
        $prefix = $this->entityType === 'pet' ? 'pet_' : 'user_';
        $cacheKey = "{$prefix}{$this->entityId}_activities_{$this->typeFilter}_{$this->dateFilter}_page{$this->page}";
        
        // Cache the activities query results
        $activities = Cache::remember($cacheKey, now()->addMinutes(5), function() {
            if ($this->entityType === 'pet') {
                $query = PetActivity::where('pet_id', $this->entityId);
            } else {
                $query = UserActivity::where('user_id', $this->entityId);
            }
            
            if ($this->typeFilter) {
                $query->where('activity_type', $this->typeFilter);
            }
            
            if ($this->dateFilter) {
                if ($this->dateFilter === 'today') {
                    $query->whereDate('created_at', today());
                } elseif ($this->dateFilter === 'week') {
                    $query->where('created_at', '>=', now()->subWeek());
                } elseif ($this->dateFilter === 'month') {
                    $query->where('created_at', '>=', now()->subMonth());
                }
            }
            
            return $query->orderBy('created_at', 'desc')
                ->paginate($this->perPage);
        });
        
        // Get friend activities if enabled
        $friendActivities = $this->showFriendActivities ? $this->getFriendActivities() : collect();
        
        // Get activity statistics (already cached in the method)
        $stats = $this->getActivityStatistics();
        
        // Cache activity types
        $activityTypes = Cache::remember($this->entityType . '_activity_types', now()->addDay(), function() {
            if ($this->entityType === 'pet') {
                return [
                    'walk' => __('friends.activity_walk'),
                    'play' => __('friends.activity_play'),
                    'meal' => __('friends.activity_meal'),
                    'sleep' => __('friends.activity_sleep'),
                    'vet_visit' => __('friends.activity_vet_visit'),
                    'grooming' => __('friends.activity_grooming'),
                    'training' => __('friends.activity_training'),
                    'medication' => __('friends.activity_medication'),
                    'friend_added' => __('friends.activity_friend_added'),
                    'friend_request_sent' => __('friends.activity_friend_request_sent'),
                    'friend_request_accepted' => __('friends.activity_friend_request_accepted'),
                    'photo_added' => __('friends.activity_photo_added'),
                    'achievement' => __('friends.activity_achievement'),
                    'birthday' => __('friends.activity_birthday'),
                ];
            } else {
                return [
                    'login' => __('friends.activity_login'),
                    'logout' => __('friends.activity_logout'),
                    'profile_update' => __('friends.activity_profile_update'),
                    'friend_request_sent' => __('friends.activity_friend_request_sent'),
                    'friend_request_accepted' => __('friends.activity_friend_request_accepted'),
                    'friend_request_declined' => __('friends.activity_friend_request_declined'),
                    'post_created' => __('friends.activity_post_created'),
                    'post_liked' => __('friends.activity_post_liked'),
                    'post_commented' => __('friends.activity_post_commented'),
                    'post_shared' => __('friends.activity_post_shared'),
                    'pet_added' => __('friends.activity_pet_added'),
                    'pet_updated' => __('friends.activity_pet_updated'),
                    'pet_removed' => __('friends.activity_pet_removed'),
                    'followed_user' => __('friends.activity_followed_user'),
                    'unfollowed_user' => __('friends.activity_unfollowed_user'),
                ];
            }
        });
        
        return view('livewire.common.activity-log', [
            'entity' => $this->getEntity(),
            'activities' => $activities,
            'friendActivities' => $friendActivities,
            'stats' => $stats,
            'activityTypes' => $activityTypes
        ]);
    }
}
