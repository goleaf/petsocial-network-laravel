<?php

namespace App\Models\Group;

use App\Models\AbstractModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Cache;

class Role extends AbstractModel
{
    use HasFactory;

    protected $table = 'group_roles';

    protected $fillable = [
        'group_id',
        'name',
        'color',
        'description',
        'permissions',
        'priority',
        'is_default',
    ];

    protected $casts = [
        'permissions' => 'array',
        'priority' => 'integer',
        'is_default' => 'boolean',
    ];
    
    /**
     * The "booted" method of the model.
     */
    protected static function booted()
    {
        parent::booted();
        
        static::created(function ($role) {
            $role->clearCache();
            $role->group->clearCache();
        });
        
        static::updated(function ($role) {
            $role->clearCache();
            $role->group->clearCache();
        });
        
        static::deleted(function ($role) {
            $role->clearCache();
            $role->group->clearCache();
        });
    }

    /**
     * Get the group that owns the role.
     */
    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * Get the users that have this role.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'group_user_roles', 'group_role_id', 'group_user_id')
            ->using(UserRole::class);
    }
    
    /**
     * Get the users count with caching
     */
    public function getUsersCountAttribute()
    {
        $cacheKey = $this->generateCacheKey('users_count');
        
        return Cache::remember($cacheKey, now()->addHours(1), function () {
            return $this->users()->count();
        });
    }

    /**
     * Check if this role has a specific permission.
     */
    public function hasPermission($permission)
    {
        $cacheKey = $this->generateCacheKey("permission_{$permission}");
        
        return Cache::remember($cacheKey, now()->addDay(), function () use ($permission) {
            return isset($this->permissions[$permission]) && $this->permissions[$permission];
        });
    }

    /**
     * Set a specific permission.
     */
    public function setPermission($permission, $value = true)
    {
        $permissions = $this->permissions ?: [];
        $permissions[$permission] = $value;
        $this->permissions = $permissions;
        
        // Clear the permission cache
        Cache::forget($this->generateCacheKey("permission_{$permission}"));
        
        return $this;
    }

    /**
     * Remove a specific permission.
     */
    public function removePermission($permission)
    {
        $permissions = $this->permissions ?: [];
        unset($permissions[$permission]);
        $this->permissions = $permissions;
        
        // Clear the permission cache
        Cache::forget($this->generateCacheKey("permission_{$permission}"));
        
        return $this;
    }

    /**
     * Scope a query to only include default roles.
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Scope a query to order by priority (highest first).
     */
    public function scopeOrderByPriority($query)
    {
        return $query->orderBy('priority', 'desc');
    }
}
