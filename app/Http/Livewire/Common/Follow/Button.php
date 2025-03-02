<?php

namespace App\Http\Livewire\Common\Follow;

use App\Models\User;
use App\Traits\EntityTypeTrait;
use Livewire\Component;

class Button extends Component
{
    use EntityTypeTrait;
    
    public $targetId;
    public $isFollowing = false;
    public $isReceivingNotifications = false;
    
    protected $listeners = ['refresh' => '$refresh'];
    
    public function mount($entityType = 'user', $entityId = null, $targetId = null)
    {
        $this->entityType = $entityType;
        $this->entityId = $entityId ?? auth()->id();
        $this->targetId = $targetId;
        
        if (!$this->targetId) {
            throw new \InvalidArgumentException("Target ID is required");
        }
        
        $this->refreshStatus();
    }
    
    public function refreshStatus()
    {
        $entity = $this->getEntity();
        $target = User::findOrFail($this->targetId);
        
        $this->isFollowing = $entity->isFollowing($target);
        $this->isReceivingNotifications = $entity->isReceivingNotificationsFrom($target);
    }
    
    public function follow()
    {
        $entity = $this->getEntity();
        $target = User::findOrFail($this->targetId);
        
        if (!$entity->isFollowing($target)) {
            $entity->follow($target);
            $this->isFollowing = true;
            $this->isReceivingNotifications = true;
            
            $this->emit('userFollowed', $this->targetId);
            $this->emit('refresh');
        }
    }
    
    public function unfollow()
    {
        $entity = $this->getEntity();
        $target = User::findOrFail($this->targetId);
        
        if ($entity->isFollowing($target)) {
            $entity->unfollow($target);
            $this->isFollowing = false;
            $this->isReceivingNotifications = false;
            
            $this->emit('userUnfollowed', $this->targetId);
            $this->emit('refresh');
        }
    }
    
    public function toggleNotifications()
    {
        $entity = $this->getEntity();
        $target = User::findOrFail($this->targetId);
        
        if ($entity->isFollowing($target)) {
            if ($entity->isReceivingNotificationsFrom($target)) {
                $entity->muteNotificationsFrom($target);
                $this->isReceivingNotifications = false;
            } else {
                $entity->unmuteNotificationsFrom($target);
                $this->isReceivingNotifications = true;
            }
            
            $this->emit('notificationsToggled', $this->targetId);
            $this->emit('refresh');
        }
    }
    
    public function render()
    {
        return view('livewire.common.follow.button');
    }
}
