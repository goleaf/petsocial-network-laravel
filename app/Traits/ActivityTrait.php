<?php

namespace App\Traits;

use App\Models\PetActivity;
use App\Models\UserActivity;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;

trait ActivityTrait
{
    /**
     * Get recent activities for an entity
     *
     * @param int $limit
     * @param string|null $filter
     * @return Collection
     */
    public function getRecentActivities(int $limit = 10, ?string $filter = null): Collection
    {
        $entity = $this->getEntity();
        $prefix = $this->entityType === 'pet' ? 'pet_' : 'user_';
        $cacheKey = "{$prefix}{$this->entityId}_recent_activities_{$limit}_{$filter}";
        
        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($entity, $limit, $filter) {
            if ($this->entityType === 'pet') {
                $query = $entity->activities();
                
                if ($filter) {
                    $query->where('type', $filter);
                }
                
                return $query->with('pet')
                    ->orderBy('created_at', 'desc')
                    ->take($limit)
                    ->get();
            } else {
                // For users, we need to handle activities differently
                $query = $entity->activities();
                
                if ($filter) {
                    $query->where('type', $filter);
                }
                
                return $query->with('user')
                    ->orderBy('created_at', 'desc')
                    ->take($limit)
                    ->get();
            }
        });
    }
    
    /**
     * Get friend activities
     *
     * @param int $limit
     * @param string|null $filter
     * @param string $timeFrame
     * @return Collection
     */
    public function getFriendActivities(int $limit = 10, ?string $filter = null, string $timeFrame = 'week'): Collection
    {
        $friendIds = $this->getFriendIds();
        
        if (empty($friendIds)) {
            return collect();
        }
        
        $prefix = $this->entityType === 'pet' ? 'pet_' : 'user_';
        $cacheKey = "{$prefix}{$this->entityId}_friend_activities_{$limit}_{$filter}_{$timeFrame}";
        
        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($friendIds, $limit, $filter, $timeFrame) {
            $query = null;
            
            if ($this->entityType === 'pet') {
                // For pets, get activities from pet_activities table
                $activityModel = \App\Models\PetActivity::class;
                $query = $activityModel::whereIn('pet_id', $friendIds);
            } else {
                // For users, get activities from user_activities table
                $activityModel = \App\Models\UserActivity::class;
                $query = $activityModel::whereIn('user_id', $friendIds);
            }
            
            // Apply time frame filter
            switch ($timeFrame) {
                case 'day':
                    $query->where('created_at', '>=', now()->subDay());
                    break;
                case 'week':
                    $query->where('created_at', '>=', now()->subWeek());
                    break;
                case 'month':
                    $query->where('created_at', '>=', now()->subMonth());
                    break;
            }
            
            // Apply activity type filter if provided
            if ($filter) {
                $query->where('type', $filter);
            }
            
            // Get the activities with eager loading
            if ($this->entityType === 'pet') {
                return $query->with('pet')
                    ->orderBy('created_at', 'desc')
                    ->take($limit)
                    ->get();
            } else {
                return $query->with('user')
                    ->orderBy('created_at', 'desc')
                    ->take($limit)
                    ->get();
            }
        });
    }
    
    /**
     * Log an activity for the entity
     *
     * @param string $type
     * @param array $data
     * @return Model
     */
    public function logActivity(string $type, array $data = []): \Illuminate\Database\Eloquent\Model
    {
        $entity = $this->getEntity();
        
        if ($this->entityType === 'pet') {
            $activity = $entity->activities()->create([
                'type' => $type,
                'data' => $data,
            ]);
        } else {
            $activity = $entity->activities()->create([
                'type' => $type,
                'data' => $data,
            ]);
        }
        
        // Clear cache
        $prefix = $this->entityType === 'pet' ? 'pet_' : 'user_';
        Cache::forget("{$prefix}{$this->entityId}_recent_activities_10_");
        
        return $activity;
    }
    
    /**
     * Delete an activity
     *
     * @param int $activityId
     * @return void
     */
    public function deleteActivity(int $activityId): void
    {
        $entity = $this->getEntity();
        
        if ($this->entityType === 'pet') {
            $entity->activities()->where('id', $activityId)->delete();
        } else {
            $entity->activities()->where('id', $activityId)->delete();
        }
        
        // Clear cache
        $prefix = $this->entityType === 'pet' ? 'pet_' : 'user_';
        Cache::forget("{$prefix}{$this->entityId}_recent_activities_10_");
    }
    
    /**
     * Get the activity model class based on entity type
     *
     * @return string
     */
    public function getActivityModel(): string
    {
        return $this->entityType === 'pet' ? PetActivity::class : UserActivity::class;
    }
}
