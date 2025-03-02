<?php

namespace App\Models\Group;

use App\Models\AbstractModel;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class Category extends AbstractModel
{
    protected $table = 'group_categories';

    protected $fillable = [
        'name',
        'slug',
        'icon',
        'color',
        'description',
        'display_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted()
    {
        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
        
        static::created(function ($category) {
            $category->clearCache();
            Cache::forget('group_categories_active');
            Cache::forget('group_categories_all');
        });
        
        static::updated(function ($category) {
            $category->clearCache();
            Cache::forget('group_categories_active');
            Cache::forget('group_categories_all');
        });
        
        static::deleted(function ($category) {
            $category->clearCache();
            Cache::forget('group_categories_active');
            Cache::forget('group_categories_all');
        });
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * Get the groups in this category.
     */
    public function groups()
    {
        return $this->hasMany(Group::class, 'category_id');
    }
    
    /**
     * Get the count of groups in this category
     */
    public function getGroupsCountAttribute()
    {
        $cacheKey = $this->generateCacheKey('groups_count');
        
        return Cache::remember($cacheKey, now()->addHours(1), function () {
            return $this->groups()->count();
        });
    }
    
    /**
     * Get the active groups in this category
     */
    public function getActiveGroupsAttribute()
    {
        $cacheKey = $this->generateCacheKey('active_groups');
        
        return Cache::remember($cacheKey, now()->addHours(1), function () {
            return $this->groups()->where('is_active', true)->get();
        });
    }

    /**
     * Scope a query to only include active categories.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to order categories by display order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order');
    }
    
    /**
     * Get all active categories with caching
     */
    public static function getActiveCategories()
    {
        return Cache::remember('group_categories_active', now()->addHours(6), function () {
            return static::active()->ordered()->get();
        });
    }
    
    /**
     * Get all categories with caching
     */
    public static function getAllCategories()
    {
        return Cache::remember('group_categories_all', now()->addHours(6), function () {
            return static::ordered()->get();
        });
    }
}
