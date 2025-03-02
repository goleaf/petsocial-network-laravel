<?php

namespace App\Http\Livewire\Pet;

use App\Models\Pet;
use App\Models\PetActivity;
use App\Models\PetNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class ActivityLog extends Component
{
    use WithFileUploads, WithPagination;
    
    public $petId;
    public $pet;
    
    // Form fields
    public $activityType = 'walk';
    public $description;
    public $location;
    public $happenedAt;
    public $image;
    public $isPublic = true;
    
    // Edit mode
    public $editMode = false;
    public $activityId;
    
    // Filters
    public $typeFilter = '';
    public $dateFilter = '';
    
    // Friend activities
    public $showFriendActivities = false;
    
    protected $paginationTheme = 'tailwind';
    
    protected $listeners = [
        'refreshActivities' => '$refresh'
    ];
    
    /**
     * Initialize the component
     *
     * @param int $petId
     * @return void
     */
    public function mount($petId)
    {
        $this->petId = $petId;
        $this->pet = Pet::with('user')->findOrFail($petId);
        
        // Check if the current user is the owner
        if ($this->pet->user_id !== auth()->id()) {
            abort(403, 'You do not have permission to manage activities for this pet.');
        }
        
        $this->happenedAt = now()->format('Y-m-d H:i');
    }
    
    protected function rules()
    {
        return [
            'activityType' => 'required|string|max:20',
            'description' => 'nullable|string|max:500',
            'location' => 'nullable|string|max:100',
            'happenedAt' => 'required|date',
            'image' => 'nullable|image|max:2048',
            'isPublic' => 'boolean',
        ];
    }
    
    public function resetForm()
    {
        $this->reset(['activityType', 'description', 'location', 'image', 'editMode', 'activityId']);
        $this->happenedAt = now()->format('Y-m-d H:i');
        $this->isPublic = true;
        $this->resetErrorBag();
        $this->resetValidation();
    }
    
    /**
     * Save a new activity
     *
     * @return void
     */
    public function save()
    {
        $this->validate();
        
        $data = [
            'pet_id' => $this->petId,
            'activity_type' => $this->activityType,
            'description' => $this->description,
            'location' => $this->location,
            'happened_at' => $this->happenedAt,
            'is_public' => $this->isPublic,
        ];
        
        if ($this->image) {
            $data['image'] = $this->image->store('pet-activities', 'public');
        }
        
        $activity = null;
        
        DB::transaction(function () use ($data, &$activity) {
            $activity = PetActivity::create($data);
            
            // If activity is public, notify friends
            if ($this->isPublic) {
                $this->notifyFriendsAboutActivity($activity);
            }
        });
        
        // Clear pet activities cache
        $this->clearActivityCache();
        
        $this->resetForm();
        
        session()->flash('message', 'Activity logged successfully!');
        $this->dispatchBrowserEvent('activity-saved');
        $this->emit('refreshActivities');
    }
    
    public function edit($activityId)
    {
        $activity = PetActivity::findOrFail($activityId);
        
        // Check if the activity belongs to the current pet
        if ($activity->pet_id !== $this->petId) {
            abort(403);
        }
        
        $this->activityId = $activity->id;
        $this->activityType = $activity->activity_type;
        $this->description = $activity->description;
        $this->location = $activity->location;
        $this->happenedAt = $activity->happened_at->format('Y-m-d H:i');
        $this->isPublic = $activity->is_public;
        $this->editMode = true;
    }
    
    /**
     * Update an existing activity
     *
     * @return void
     */
    public function update()
    {
        $this->validate();
        
        $activity = PetActivity::findOrFail($this->activityId);
        
        // Check if the activity belongs to the current pet
        if ($activity->pet_id !== $this->petId) {
            abort(403, 'You do not have permission to update this activity.');
        }
        
        $wasPublic = $activity->is_public;
        
        $data = [
            'activity_type' => $this->activityType,
            'description' => $this->description,
            'location' => $this->location,
            'happened_at' => $this->happenedAt,
            'is_public' => $this->isPublic,
        ];
        
        if ($this->image) {
            // Delete old image if exists
            if ($activity->image && Storage::disk('public')->exists($activity->image)) {
                Storage::disk('public')->delete($activity->image);
            }
            $data['image'] = $this->image->store('pet-activities', 'public');
        }
        
        DB::transaction(function () use ($activity, $data, $wasPublic) {
            $activity->update($data);
            
            // If activity was private and is now public, notify friends
            if (!$wasPublic && $this->isPublic) {
                $this->notifyFriendsAboutActivity($activity);
            }
        });
        
        // Clear pet activities cache
        $this->clearActivityCache();
        
        $this->resetForm();
        
        session()->flash('message', 'Activity updated successfully!');
        $this->dispatchBrowserEvent('activity-updated');
        $this->emit('refreshActivities');
    }
    
    /**
     * Delete an activity
     *
     * @param int $activityId
     * @return void
     */
    public function delete($activityId)
    {
        $activity = PetActivity::findOrFail($activityId);
        
        // Check if the activity belongs to the current pet
        if ($activity->pet_id !== $this->petId) {
            abort(403, 'You do not have permission to delete this activity.');
        }
        
        DB::transaction(function () use ($activity) {
            // Delete image if exists
            if ($activity->image && Storage::disk('public')->exists($activity->image)) {
                Storage::disk('public')->delete($activity->image);
            }
            
            $activity->delete();
        });
        
        // Clear pet activities cache
        $this->clearActivityCache();
        
        session()->flash('message', 'Activity deleted successfully!');
        $this->emit('refreshActivities');
    }
    
    public function cancelEdit()
    {
        $this->resetForm();
    }
    
    /**
     * Clear activity-related cache
     *
     * @return void
     */
    private function clearActivityCache()
    {
        // Clear pet's own activity caches
        Cache::forget("pet_{$this->petId}_recent_activities_5");
        Cache::forget("pet_{$this->petId}_activity_stats");
        
        // Clear all filtered activity caches
        $this->clearFilteredActivityCaches();
        
        // Clear friend activity caches for pets that are friends with this pet
        $friendIds = $this->getCachedFriendIds();
        
        foreach ($friendIds as $friendId) {
            // Clear basic friend activity cache
            Cache::forget("pet_{$friendId}_friend_activities");
            
            // Clear filtered friend activity caches
            $this->clearFilteredActivityCaches($friendId);
        }
    }
    
    /**
     * Clear all filtered activity caches for a pet
     *
     * @param int|null $petId The pet ID to clear caches for (defaults to current pet)
     * @return void
     */
    private function clearFilteredActivityCaches($petId = null)
    {
        $petId = $petId ?? $this->petId;
        
        // Clear all possible filter combinations
        $activityTypes = array_keys(PetActivity::getActivityTypes());
        $activityTypes[] = ''; // Add empty filter
        
        foreach ($activityTypes as $type) {
            // Clear without date filter
            Cache::forget("pet_{$petId}_friend_activities_{$type}_");
            
            // Clear with recent date filters (last 7 days)
            for ($i = 0; $i < 7; $i++) {
                $date = now()->subDays($i)->format('Y-m-d');
                Cache::forget("pet_{$petId}_friend_activities_{$type}_{$date}");
            }
        }
    }
    
    /**
     * Get cached friend IDs for the current pet
     *
     * @return array
     */
    private function getCachedFriendIds()
    {
        $cacheKey = "pet_{$this->petId}_friend_ids";
        return Cache::remember($cacheKey, now()->addHours(1), function() {
            $pet = Pet::findOrFail($this->petId);
            return $pet->getFriendIds();
        });
    }
    
    /**
     * Toggle showing friend activities
     */
    public function toggleFriendActivities()
    {
        $this->showFriendActivities = !$this->showFriendActivities;
        // Clear filtered activity caches when toggling friend activities
        $this->clearFilteredActivityCaches();
    }
    
    /**
     * Handle type filter updates
     *
     * @return void
     */
    public function updatedTypeFilter()
    {
        $this->resetPage();
        // Clear filtered activity caches when filter changes
        $this->clearFilteredActivityCaches();
    }
    
    /**
     * Handle date filter updates
     *
     * @return void
     */
    public function updatedDateFilter()
    {
        $this->resetPage();
        // Clear filtered activity caches when filter changes
        $this->clearFilteredActivityCaches();
    }
    
    /**
     * Get activities from friends
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function getFriendActivities()
    {
        // Get friend IDs with caching
        $friendIds = $this->getCachedFriendIds();
        
        if (empty($friendIds)) {
            return collect();
        }
        
        // Get friend activities with caching
        $cacheKey = "pet_{$this->petId}_friend_activities_{$this->typeFilter}_{$this->dateFilter}";
        return Cache::remember($cacheKey, now()->addMinutes(15), function() use ($friendIds) {
            $query = PetActivity::whereIn('pet_id', $friendIds)
                ->with('pet') // Eager load pet relationship
                ->where('is_public', true); // Only show public activities
                
            // Apply filters if set
            if ($this->typeFilter) {
                $query->where('activity_type', $this->typeFilter);
            }
            
            if ($this->dateFilter) {
                $date = Carbon::parse($this->dateFilter);
                $query->whereDate('happened_at', $date);
            }
            
            return $query->orderBy('happened_at', 'desc')
                ->limit(20) // Increased limit for better user experience
                ->get();
        });
    }
    
    /**
     * Notify friends about a new activity
     *
     * @param PetActivity $activity
     * @return void
     */
    protected function notifyFriendsAboutActivity(PetActivity $activity)
    {
        $pet = Pet::findOrFail($this->petId);
        $friendIds = $pet->getFriendIds();
        
        if (empty($friendIds)) {
            return;
        }
        
        foreach ($friendIds as $friendId) {
            PetNotification::createActivity(
                $friendId,
                $this->petId,
                $activity->id,
                $activity->activity_type
            );
            
            // Clear notification cache for the friend
            Cache::forget("pet_{$friendId}_unread_notifications_count");
        }
    }
    
    /**
     * Export activities to CSV
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function exportActivities()
    {
        $activities = PetActivity::where('pet_id', $this->petId)
            ->orderBy('happened_at', 'desc')
            ->get();
            
        $activityTypes = PetActivity::getActivityTypes();
        
        $filename = $this->pet->name . '_activities_' . now()->format('Y-m-d') . '.csv';
        
        return response()->streamDownload(function() use ($activities, $activityTypes) {
            $file = fopen('php://output', 'w');
            
            // Add headers
            fputcsv($file, ['Type', 'Date', 'Time', 'Location', 'Description', 'Public']);
            
            foreach ($activities as $activity) {
                $typeName = $activityTypes[$activity->activity_type] ?? $activity->activity_type;
                
                fputcsv($file, [
                    $typeName,
                    $activity->happened_at->format('Y-m-d'),
                    $activity->happened_at->format('H:i'),
                    $activity->location,
                    $activity->description,
                    $activity->is_public ? 'Yes' : 'No',
                ]);
            }
            
            fclose($file);
        }, $filename);
    }
    
    /**
     * Render the component
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        // Generate cache key based on current filters and pagination
        $cacheKey = "pet_{$this->petId}_activities_{$this->typeFilter}_{$this->dateFilter}_page{$this->page}";
        
        // Cache the activities query results
        $activities = Cache::remember($cacheKey, now()->addMinutes(5), function() {
            $query = PetActivity::where('pet_id', $this->petId);
            
            if ($this->typeFilter) {
                $query->where('activity_type', $this->typeFilter);
            }
            
            if ($this->dateFilter) {
                $date = Carbon::parse($this->dateFilter);
                $query->whereDate('happened_at', $date);
            }
            
            // Use eager loading for better performance
            return $query->with('pet')
                       ->orderBy('happened_at', 'desc')
                       ->paginate(10);
        });
        
        // Get friend activities if enabled (already cached in the method)
        $friendActivities = $this->showFriendActivities ? $this->getFriendActivities() : collect();
        
        // Get activity statistics (already cached in the method)
        $stats = $this->getActivityStatistics();
        
        // Cache activity types
        $activityTypes = Cache::remember('pet_activity_types', now()->addDay(), function() {
            return PetActivity::getActivityTypes();
        });
        
        return view('livewire.common.activity-log', [
            'pet' => $this->pet,
            'activities' => $activities,
            'friendActivities' => $friendActivities,
            'activityTypes' => $activityTypes,
            'stats' => $stats,
        ]);
    }
    
    /**
     * Get activity statistics
     *
     * @return array
     */
    protected function getActivityStatistics()
    {
        $cacheKey = "pet_{$this->petId}_activity_stats";
        
        return Cache::remember($cacheKey, now()->addHours(3), function() {
            // Use a single query to get total count
            $totalCount = PetActivity::where('pet_id', $this->petId)->count();
            
            // Activities by type - optimized query
            $byType = Cache::remember("pet_{$this->petId}_activities_by_type", now()->addHours(3), function() {
                return PetActivity::where('pet_id', $this->petId)
                    ->select('activity_type', DB::raw('count(*) as count'))
                    ->groupBy('activity_type')
                    ->pluck('count', 'activity_type')
                    ->toArray();
            });
            
            // Activities by month (last 6 months) - optimized query
            $sixMonthsAgo = now()->subMonths(6);
            $byMonth = Cache::remember("pet_{$this->petId}_activities_by_month", now()->addHours(3), function() use ($sixMonthsAgo) {
                return PetActivity::where('pet_id', $this->petId)
                    ->where('happened_at', '>=', $sixMonthsAgo)
                    ->select(DB::raw('DATE_FORMAT(happened_at, "%Y-%m") as month'), DB::raw('count(*) as count'))
                    ->groupBy('month')
                    ->orderBy('month')
                    ->pluck('count', 'month')
                    ->toArray();
            });
            
            // Most active locations - optimized query
            $topLocations = Cache::remember("pet_{$this->petId}_top_locations", now()->addHours(3), function() {
                return PetActivity::where('pet_id', $this->petId)
                    ->whereNotNull('location')
                    ->select('location', DB::raw('count(*) as count'))
                    ->groupBy('location')
                    ->orderBy('count', 'desc')
                    ->limit(5)
                    ->pluck('count', 'location')
                    ->toArray();
            });
            
            return [
                'total' => $totalCount,
                'by_type' => $byType,
                'by_month' => $byMonth,
                'top_locations' => $topLocations,
            ];
        });
    }
}
