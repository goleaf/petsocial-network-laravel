<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

abstract class AbstractModel extends Model
{
    use HasFactory;
    
    /**
     * Clear model cache with a given key pattern
     *
     * @param string $pattern
     * @return void
     */
    protected function clearModelCache(string $pattern): void
    {
        // Get all cache keys matching the pattern
        $keys = Cache::getStore()->many([$pattern . '*']);
        
        // Forget each key
        foreach (array_keys($keys) as $key) {
            Cache::forget($key);
        }
    }
    
    /**
     * Generate a cache key for this model
     *
     * @param string $type
     * @return string
     */
    protected function generateCacheKey(string $type): string
    {
        $modelName = strtolower(class_basename($this));
        return "{$modelName}_{$this->id}_{$type}";
    }
    
    /**
     * Get the model's primary cache key pattern
     * 
     * @return string
     */
    protected function getCacheKeyPattern(): string
    {
        $modelName = strtolower(class_basename($this));
        return "{$modelName}_{$this->id}_";
    }
    
    /**
     * Clear all cache related to this model
     * 
     * @return void
     */
    public function clearCache(): void
    {
        $this->clearModelCache($this->getCacheKeyPattern());
    }
}
