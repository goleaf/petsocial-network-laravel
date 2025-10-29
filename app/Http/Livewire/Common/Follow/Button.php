<?php

namespace App\Http\Livewire\Common\Follow;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use App\Traits\EntityTypeTrait;
use Livewire\Component;

class Button extends Component
{
    use EntityTypeTrait;

    /**
     * Resolver callback that allows tests to supply synthetic users without alias mocks.
     */
    protected static $userResolver = null;

    public $targetId;
    public $isFollowing = false;
    public $isReceivingNotifications = false;

    protected $listeners = ['refresh' => '$refresh'];

    /**
     * Allow downstream callers to override the target user resolution logic.
     */
    public static function resolveUserUsing(?callable $resolver): void
    {
        // Storing the resolver gives the unit tests a seam for injecting friendly stubs.
        static::$userResolver = $resolver;
    }
    
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
        $target = $this->resolveTargetUser();

        $this->isFollowing = $entity->isFollowing($target);
        $this->isReceivingNotifications = $entity->isReceivingNotificationsFrom($target);
    }

    public function follow()
    {
        $entity = $this->getEntity();
        $target = $this->resolveTargetUser();

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
        $target = $this->resolveTargetUser();

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
        $target = $this->resolveTargetUser();

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

    /**
     * Resolve the target user via the override seam or fall back to the model lookup.
     */
    protected function resolveTargetUser(): Model
    {
        if (static::$userResolver !== null) {
            // Invoke the injected resolver to keep the component flexible in isolated tests.
            $resolved = call_user_func(static::$userResolver, $this->targetId);

            if ($resolved instanceof Model) {
                return $resolved;
            }
        }

        // Default to the production behaviour when no resolver override is configured.
        return User::findOrFail($this->targetId);
    }
}
