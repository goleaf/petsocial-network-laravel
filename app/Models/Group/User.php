<?php

namespace App\Models\Group;

use App\Models\User as BaseUser;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Facades\Cache;

class User extends Pivot
{
    /**
     * Explicitly declare the membership table backing this pivot model.
     */
    protected $table = 'group_members';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'joined_at' => 'datetime',
        'approved_at' => 'datetime',
        'is_approved' => 'boolean',
        'is_admin' => 'boolean',
    ];
    
    /**
     * Generate a cache key for this model
     */
    protected function generateCacheKey(string $key): string
    {
        return "group_member_{$this->id}_{$key}";
    }
    
    /**
     * Clear all cache for this model
     */
    public function clearCache(): void
    {
        $keys = [
            $this->generateCacheKey('roles'),
            $this->generateCacheKey('has_permission'),
            $this->generateCacheKey('has_role'),
        ];
        
        foreach ($keys as $key) {
            Cache::forget($key);
        }
    }

    /**
     * Get the group that the user is a member of.
     */
    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * Get the user that is a member of the group.
     */
    public function user()
    {
        return $this->belongsTo(BaseUser::class);
    }

    /**
     * Get the roles assigned to this group user.
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'group_user_roles', 'group_user_id', 'group_role_id')
            ->using(UserRole::class)
            ->withTimestamps();
    }
    
    /**
     * Get the roles with caching
     */
    public function getRolesAttribute()
    {
        $cacheKey = $this->generateCacheKey('roles');
        
        return Cache::remember($cacheKey, now()->addHours(1), function () {
            return $this->roles()->get();
        });
    }

    /**
     * Check if the user has a specific role in the group.
     */
    public function hasRole($roleName)
    {
        $cacheKey = $this->generateCacheKey("has_role_{$roleName}");
        
        return Cache::remember($cacheKey, now()->addHours(1), function () use ($roleName) {
            return $this->roles()->where('name', $roleName)->exists();
        });
    }

    /**
     * Check if the user has a specific permission in the group.
     */
    public function hasPermission($permission)
    {
        $cacheKey = $this->generateCacheKey("has_permission_{$permission}");
        
        return Cache::remember($cacheKey, now()->addHours(1), function () use ($permission) {
            foreach ($this->roles as $role) {
                if ($role->hasPermission($permission)) {
                    return true;
                }
            }
    
            // Admin users have all permissions
            if ($this->is_admin) {
                return true;
            }
    
            return false;
        });
    }

    /**
     * Assign a role to the user in the group.
     */
    public function assignRole($roleName)
    {
        $role = $this->group->roles()->where('name', $roleName)->first();
        
        if ($role && !$this->hasRole($roleName)) {
            $this->roles()->attach($role->id);
            $this->clearCache();
            $this->group->clearCache();
        }

        return $this;
    }

    /**
     * Remove a role from the user in the group.
     */
    public function removeRole($roleName)
    {
        $role = $this->group->roles()->where('name', $roleName)->first();
        
        if ($role) {
            $this->roles()->detach($role->id);
            $this->clearCache();
            $this->group->clearCache();
        }

        return $this;
    }
}
