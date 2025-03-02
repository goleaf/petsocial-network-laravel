<?php

namespace App\Models;

use App\Traits\EntityTypeTrait;
use App\Traits\HasFriendships;
use App\Traits\ActivityTrait;
use App\Models\Traits\HasPolymorphicRelations;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class Pet extends Model
{
    use EntityTypeTrait, HasFriendships, ActivityTrait, HasPolymorphicRelations;

    protected $fillable = [
        'user_id', 
        'name', 
        'type', 
        'breed', 
        'birthdate', 
        'avatar', 
        'location', 
        'bio', 
        'favorite_food', 
        'favorite_toy',
        'is_public'
    ];
    
    protected $casts = [
        'birthdate' => 'date',
        'is_public' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Get posts associated with this pet
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    /**
     * Initialize the entity type and ID for the Pet model
     */
    protected static function boot()
    {
        parent::boot();
        
        static::created(function ($pet) {
            $pet->initializeEntity('pet', $pet->id);
        });
        
        static::retrieved(function ($pet) {
            $pet->initializeEntity('pet', $pet->id);
        });
    }
    
    /**
     * Get the pet's avatar URL with a default if none exists
     */
    protected function avatarUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->avatar 
                ? asset('storage/' . $this->avatar) 
                : asset('images/default-pet-avatar.png')
        );
    }
    
    /**
     * Get the pet's age based on birthdate
     */
    protected function age(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->birthdate) {
                    return null;
                }
                
                $interval = Carbon::now()->diff($this->birthdate);
                
                if ($interval->y > 0) {
                    return $interval->y . ' ' . ($interval->y == 1 ? 'year' : 'years');
                } elseif ($interval->m > 0) {
                    return $interval->m . ' ' . ($interval->m == 1 ? 'month' : 'months');
                } else {
                    return $interval->d . ' ' . ($interval->d == 1 ? 'day' : 'days');
                }
            }
        );
    }
    
    /**
     * Get the activities for this pet
     */
    public function activities(): HasMany
    {
        return $this->hasMany(PetActivity::class)->orderBy('happened_at', 'desc');
    }
    
    /**
     * Get the recent activities for this pet with caching
     */
    public function recentActivities($limit = 5)
    {
        $cacheKey = "pet_{$this->id}_recent_activities_{$limit}";
        
        return Cache::remember($cacheKey, now()->addMinutes(15), function () use ($limit) {
            return $this->activities()->limit($limit)->get();
        });
    }
    
    /**
     * Get the notifications for this pet
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(PetNotification::class, 'pet_id')->orderBy('created_at', 'desc');
    }
    
    /**
     * Get unread notifications count with caching
     */
    public function unreadNotificationsCount(): int
    {
        $cacheKey = "pet_{$this->id}_unread_notifications_count";
        
        return Cache::remember($cacheKey, now()->addMinutes(5), function () {
            return $this->notifications()->where('read', false)->count();
        });
    }
    
    /**
     * Mark all notifications as read
     */
    public function markAllNotificationsAsRead(): void
    {
        $this->notifications()->where('read', false)->update(['read' => true]);
        Cache::forget("pet_{$this->id}_unread_notifications_count");
    }
    
    /**
     * Export friends to CSV
     */
    public function exportFriendsToCSV(): string
    {
        $friendships = DB::table('pet_friendships')
            ->where(function($query) {
                $query->where('pet_id', $this->id)
                      ->orWhere('friend_pet_id', $this->id);
            })
            ->where('status', 'accepted')
            ->get();
            
        $friends = [];
        foreach ($friendships as $friendship) {
            $friendId = $friendship->pet_id == $this->id 
                ? $friendship->friend_pet_id 
                : $friendship->pet_id;
                
            $friend = Pet::find($friendId);
            if (!$friend) continue;
            
            $friends[] = [
                'name' => $friend->name,
                'species' => $friend->species,
                'breed' => $friend->breed,
                'category' => $friendship->category,
                'since' => $friendship->created_at,
            ];
        }
        
        $filename = $this->name . '_friends_' . now()->format('Y-m-d') . '.csv';
        $path = 'exports/' . $filename;
        
        $handle = fopen(storage_path('app/public/' . $path), 'w');
        
        // Add headers
        fputcsv($handle, ['Name', 'Species', 'Breed', 'Category', 'Since']);
        
        // Add data
        foreach ($friends as $friend) {
            fputcsv($handle, $friend);
        }
        
        fclose($handle);
        
        return Storage::url($path);
    }
}
