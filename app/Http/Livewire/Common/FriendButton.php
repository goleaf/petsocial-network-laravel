<?php

namespace App\Http\Livewire\Common;

use App\Traits\EntityTypeTrait;
use App\Traits\FriendshipTrait;
use App\Traits\ActivityTrait;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class FriendButton extends Component
{
    use EntityTypeTrait, FriendshipTrait, ActivityTrait;
    
    /**
     * Whether the button is disabled
     *
     * @var bool
     */
    public $disabled = false;
    
    /**
     * The size of the button
     *
     * @var string
     */
    public $size = 'md';
    
    /**
     * The style of the button
     *
     * @var string
     */
    public $style = 'primary';
    
    /**
     * Whether to show the icon
     *
     * @var bool
     */
    public $showIcon = true;
    
    /**
     * Whether to show the text
     *
     * @var bool
     */
    public $showText = true;
    
    /**
     * Initialize the component
     *
     * @param string $entityType
     * @param int $entityId
     * @param string $size
     * @param string $style
     * @param bool $showIcon
     * @param bool $showText
     * @param bool $disabled
     * @return void
     */
    public function mount(
        string $entityType,
        int $entityId,
        string $size = 'md',
        string $style = 'primary',
        bool $showIcon = true,
        bool $showText = true,
        bool $disabled = false
    ) {
        $this->initializeEntity($entityType, $entityId);
        $this->size = $size;
        $this->style = $style;
        $this->showIcon = $showIcon;
        $this->showText = $showText;
        $this->disabled = $disabled;
    }
    
    /**
     * Send a friend request
     *
     * @return void
     */
    public function sendFriendRequest()
    {
        if ($this->disabled) {
            return;
        }
        
        $result = $this->addFriend($this->entityId);
        
        if ($result) {
            // Log activity
            $this->logFriendRequestActivity();
            
            // Clear friendship caches
            $this->clearFriendshipCaches();
            
            $this->emit('friendRequestSent', [
                'entityType' => $this->entityType,
                'entityId' => $this->entityId,
            ]);
            
            $this->dispatchBrowserEvent('friend-request-sent', [
                'message' => 'Friend request sent successfully!',
            ]);
        }
    }
    
    /**
     * Accept a friend request
     *
     * @return void
     */
    public function acceptFriendRequest()
    {
        if ($this->disabled) {
            return;
        }
        
        $result = $this->acceptFriend($this->entityId);
        
        if ($result) {
            // Log activity
            $this->logFriendAcceptedActivity();
            
            // Clear friendship caches
            $this->clearFriendshipCaches();
            
            $this->emit('friendRequestAccepted', [
                'entityType' => $this->entityType,
                'entityId' => $this->entityId,
            ]);
            
            $this->dispatchBrowserEvent('friend-request-accepted', [
                'message' => 'Friend request accepted!',
            ]);
        }
    }
    
    /**
     * Decline a friend request
     *
     * @return void
     */
    public function declineFriendRequest()
    {
        if ($this->disabled) {
            return;
        }
        
        $result = $this->declineFriend($this->entityId);
        
        if ($result) {
            // Clear friendship caches
            $this->clearFriendshipCaches();
            
            $this->emit('friendRequestDeclined', [
                'entityType' => $this->entityType,
                'entityId' => $this->entityId,
            ]);
            
            $this->dispatchBrowserEvent('friend-request-declined', [
                'message' => 'Friend request declined.',
            ]);
        }
    }
    
    /**
     * Remove a friend
     *
     * @return void
     */
    public function removeFriend()
    {
        if ($this->disabled) {
            return;
        }
        
        $result = $this->removeFriend($this->entityId);
        
        if ($result) {
            // Clear friendship caches
            $this->clearFriendshipCaches();
            
            $this->emit('friendRemoved', [
                'entityType' => $this->entityType,
                'entityId' => $this->entityId,
            ]);
            
            $this->dispatchBrowserEvent('friend-removed', [
                'message' => 'Friend removed.',
            ]);
        }
    }
    
    /**
     * Cancel a friend request
     *
     * @return void
     */
    public function cancelFriendRequest()
    {
        if ($this->disabled) {
            return;
        }
        
        $result = $this->cancelFriendRequest($this->entityId);
        
        if ($result) {
            // Clear friendship caches
            $this->clearFriendshipCaches();
            
            $this->emit('friendRequestCancelled', [
                'entityType' => $this->entityType,
                'entityId' => $this->entityId,
            ]);
            
            $this->dispatchBrowserEvent('friend-request-cancelled', [
                'message' => 'Friend request cancelled.',
            ]);
        }
    }
    
    /**
     * Log friend request activity
     *
     * @return void
     */
    protected function logFriendRequestActivity()
    {
        $activityType = $this->entityType === 'pet' ? 'friend_request_sent' : 'friend_request_sent';
        $this->createActivity($activityType, $this->getEntity(), $this->getAuthEntity());
    }
    
    /**
     * Log friend accepted activity
     *
     * @return void
     */
    protected function logFriendAcceptedActivity()
    {
        $activityType = $this->entityType === 'pet' ? 'friend_request_accepted' : 'friend_request_accepted';
        $this->createActivity($activityType, $this->getEntity(), $this->getAuthEntity());
    }
    
    /**
     * Get cached friendship status
     *
     * @return string
     */
    protected function getCachedFriendshipStatus()
    {
        $cacheKey = "{$this->entityType}_{$this->entityId}_friendship_status";
        
        return \Illuminate\Support\Facades\Cache::remember($cacheKey, now()->addMinutes(15), function() {
            return $this->getFriendshipStatus($this->entityId);
        });
    }
    
    /**
     * Clear friendship related caches
     *
     * @return void
     */
    protected function clearFriendshipCaches()
    {
        // Clear friendship status cache
        \Illuminate\Support\Facades\Cache::forget("{$this->entityType}_{$this->entityId}_friendship_status");
        
        // Clear related caches
        \Illuminate\Support\Facades\Cache::forget("{$this->entityType}_{$this->entityId}_friend_ids");
        \Illuminate\Support\Facades\Cache::forget("{$this->entityType}_{$this->entityId}_pending_friend_request_ids");
        \Illuminate\Support\Facades\Cache::forget("{$this->entityType}_{$this->entityId}_sent_friend_request_ids");
        \Illuminate\Support\Facades\Cache::forget("{$this->entityType}_{$this->entityId}_recent_friend_ids");
        \Illuminate\Support\Facades\Cache::forget("{$this->entityType}_{$this->entityId}_friends_all__name_asc_page1_perPage10");
    }
    
    /**
     * Render the component
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        $friendshipStatus = $this->getCachedFriendshipStatus();
        
        return view('livewire.common.friend-button', [
            'friendshipStatus' => $friendshipStatus,
        ]);
    }
}
