<?php

namespace App\Models;

use App\Interfaces\FriendshipInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

abstract class AbstractFriendship extends Model implements FriendshipInterface
{
    use HasFactory;
    
    /**
     * The possible friendship statuses
     */
    const STATUS_PENDING = 'pending';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_DECLINED = 'declined';
    const STATUS_BLOCKED = 'blocked';
    
    /**
     * Scope query to only include accepted friendships
     */
    public function scopeAccepted($query)
    {
        return $query->where('status', self::STATUS_ACCEPTED);
    }
    
    /**
     * Scope query to only include pending friendships
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }
    
    /**
     * Scope query to only include declined friendships
     */
    public function scopeDeclined($query)
    {
        return $query->where('status', self::STATUS_DECLINED);
    }
    
    /**
     * Scope query to only include blocked friendships
     */
    public function scopeBlocked($query)
    {
        return $query->where('status', self::STATUS_BLOCKED);
    }
    
    /**
     * Accept the friendship request.
     * 
     * @return $this
     */
    public function accept()
    {
        $this->update([
            'status' => self::STATUS_ACCEPTED,
            'accepted_at' => now(),
        ]);
        
        $this->createAcceptNotification();
        $this->clearFriendshipCache();
        
        return $this;
    }
    
    /**
     * Decline the friendship request.
     * 
     * @return $this
     */
    public function decline()
    {
        $this->update([
            'status' => self::STATUS_DECLINED,
        ]);
        
        $this->clearFriendshipCache();
        
        return $this;
    }
    
    /**
     * Block the friendship.
     * 
     * @return $this
     */
    public function block()
    {
        $this->update([
            'status' => self::STATUS_BLOCKED,
        ]);
        
        $this->clearFriendshipCache();
        
        return $this;
    }
    
    /**
     * Update the friendship category.
     * 
     * @param string|null $category
     * @return $this
     */
    public function categorize($category)
    {
        $this->update([
            'category' => $category,
        ]);
        
        $this->clearFriendshipCache();
        
        return $this;
    }
    
    /**
     * Create a notification when a friendship is accepted
     * This method should be implemented by child classes
     */
    abstract protected function createAcceptNotification(): void;
    
    /**
     * Clear friendship-related cache
     * This method should be implemented by child classes
     */
    abstract protected function clearFriendshipCache(): void;
}
