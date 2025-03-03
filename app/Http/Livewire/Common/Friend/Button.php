<?php

namespace App\Http\Livewire\Common\Friend;

use App\Models\User;
use App\Models\Pet;
use App\Models\Friendship;
use App\Models\PetFriendship;
use App\Traits\EntityTypeTrait;
use App\Traits\FriendshipTrait;
use Livewire\Component;

class Button extends Component
{
    use EntityTypeTrait, FriendshipTrait;
    
    public $targetId;
    public $status;
    public $showDropdown = false;
    
    protected $listeners = ['refresh' => '$refresh'];

    public function mount($entityType = 'user', $entityId = null, $targetId = null)
    {
        $this->entityType = $entityType;
        $this->entityId = $entityId ?? ($entityType === 'user' ? auth()->id() : null);
        $this->targetId = $targetId;
        
        if (!$this->entityId) {
            throw new \InvalidArgumentException(__('friends.entity_id_required'));
        }
        
        if (!$this->targetId) {
            throw new \InvalidArgumentException(__('friends.target_id_required'));
        }
        
        $this->refreshStatus();
    }
    
    public function refreshStatus()
    {
        $friendshipModel = $this->getFriendshipModel();
        $entityIdField = $this->getEntityIdField();
        $friendIdField = $this->getFriendIdField();
        
        // Check if there's a pending request from the entity to the target
        $sentRequest = $friendshipModel::where($entityIdField, $this->entityId)
            ->where($friendIdField, $this->targetId)
            ->where('status', 'pending')
            ->first();
            
        if ($sentRequest) {
            $this->status = 'sent_request';
            return;
        }
        
        // Check if there's a pending request from the target to the entity
        $receivedRequest = $friendshipModel::where($entityIdField, $this->targetId)
            ->where($friendIdField, $this->entityId)
            ->where('status', 'pending')
            ->first();
            
        if ($receivedRequest) {
            $this->status = 'received_request';
            return;
        }
        
        // Check if they are already friends
        if ($this->areFriends($this->targetId)) {
            $this->status = 'friends';
            return;
        }
        
        // Default status
        $this->status = 'not_friends';
    }
    
    public function sendRequest()
    {
        if (!$this->isAuthorized()) {
            $this->emit('error', __('friends.not_authorized'));
            return;
        }
        
        $this->addFriend($this->targetId);
        $this->refreshStatus();
        $this->emit('friendRequestSent', $this->targetId);
        $this->emit('refresh');
    }
    
    public function acceptRequest()
    {
        if (!$this->isAuthorized()) {
            $this->emit('error', __('friends.not_authorized'));
            return;
        }
        
        $this->acceptFriend($this->targetId);
        $this->refreshStatus();
        $this->emit('friendRequestAccepted', $this->targetId);
        $this->emit('refresh');
    }
    
    public function declineRequest()
    {
        if (!$this->isAuthorized()) {
            $this->emit('error', __('friends.not_authorized'));
            return;
        }
        
        $this->declineFriend($this->targetId);
        $this->refreshStatus();
        $this->emit('friendRequestDeclined', $this->targetId);
        $this->emit('refresh');
    }
    
    public function cancelRequest()
    {
        if (!$this->isAuthorized()) {
            $this->emit('error', __('friends.not_authorized'));
            return;
        }
        
        $this->cancelFriendRequest($this->targetId);
        $this->refreshStatus();
        $this->emit('friendRequestCancelled', $this->targetId);
        $this->emit('refresh');
    }
    
    public function removeFriendship()
    {
        if (!$this->isAuthorized()) {
            $this->emit('error', __('friends.not_authorized'));
            return;
        }
        
        $this->removeFriend($this->targetId);
        $this->refreshStatus();
        $this->emit('friendRemoved', $this->targetId);
        $this->emit('refresh');
    }
    
    public function blockEntity()
    {
        if (!$this->isAuthorized()) {
            $this->emit('error', __('friends.not_authorized'));
            return;
        }
        
        $this->blockFriend($this->targetId);
        $this->refreshStatus();
        $this->emit('entityBlocked', $this->targetId);
        $this->emit('refresh');
    }
    
    public function toggleDropdown()
    {
        $this->showDropdown = !$this->showDropdown;
    }
    
    public function render()
    {
        return view('livewire.common.friend.button', [
            'entity' => $this->getEntity(),
            'target' => $this->entityType === 'pet' 
                ? Pet::find($this->targetId) 
                : User::find($this->targetId)
        ]);
    }
}
