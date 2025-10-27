<?php

namespace App\Http\Livewire\Common;

use App\Models\Pet;
use App\Models\PetNotification;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Livewire\Component;
use Livewire\WithPagination;

class NotificationCenter extends Component
{
    use WithPagination;

    public $entityType;

    public $entityId;

    public $entity;

    public $unreadCount = 0;

    public $filter = 'all'; // all, unread, read

    public $category = 'all';

    public $priority = 'all';

    /**
     * Cached list of categories available for filtering.
     */
    public array $availableCategories = [];

    /**
     * Priorities available for filtering.
     */
    public array $availablePriorities = [];

    protected $paginationTheme = 'tailwind';

    protected $queryString = ['filter', 'category', 'priority'];

    protected $listeners = [
        'refreshNotifications' => '$refresh',
        'markAsRead' => 'markAsRead',
        'markAllAsRead' => 'markAllAsRead',
    ];

    /**
     * Initialize the component
     *
     * @param  string  $entityType
     * @param  int  $entityId
     * @return void
     */
    public function mount($entityType, $entityId)
    {
        $this->entityType = $entityType;
        $this->entityId = $entityId;

        // Load the entity
        if ($entityType === 'user') {
            $this->entity = User::findOrFail($entityId);

            // Check authorization
            if ($this->entity->id !== auth()->id()) {
                abort(403, 'You do not have permission to view these notifications.');
            }
        } elseif ($entityType === 'pet') {
            $this->entity = Pet::findOrFail($entityId);

            // Check authorization
            if ($this->entity->user_id !== auth()->id()) {
                abort(403, 'You do not have permission to view notifications for this pet.');
            }
        } else {
            abort(400, 'Invalid entity type.');
        }

        $this->availableCategories = array_keys(Config::get('notifications.categories', []));
        $this->availablePriorities = Config::get('notifications.priorities', []);

        if ($this->category !== 'all' && ! in_array($this->category, $this->availableCategories, true)) {
            $this->category = 'all';
        }

        if ($this->priority !== 'all' && ! in_array($this->priority, $this->availablePriorities, true)) {
            $this->priority = 'all';
        }

        $this->updateUnreadCount();
    }

    /**
     * Update the unread notifications count
     *
     * @return void
     */
    public function updateUnreadCount()
    {
        $cacheKey = "{$this->entityType}_{$this->entityId}_unread_notifications_count";
        $this->unreadCount = Cache::remember($cacheKey, now()->addMinutes(5), function () {
            return $this->getNotificationModel()::where($this->getEntityColumn(), $this->entityId)
                ->whereNull('read_at')
                ->count();
        });
    }

    /**
     * Mark a notification as read
     *
     * @param  int  $notificationId
     * @return void
     */
    public function markAsRead($notificationId)
    {
        $notification = $this->getNotificationModel()::where($this->getEntityColumn(), $this->entityId)
            ->findOrFail($notificationId);

        $notification->markAsRead();

        // Clear cache
        Cache::forget("{$this->entityType}_{$this->entityId}_unread_notifications_count");

        $this->updateUnreadCount();
    }

    /**
     * Mark all notifications as read
     *
     * @return void
     */
    public function markAllAsRead()
    {
        $this->getNotificationModel()::where($this->getEntityColumn(), $this->entityId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        // Clear cache
        Cache::forget("{$this->entityType}_{$this->entityId}_unread_notifications_count");

        $this->unreadCount = 0;
    }

    /**
     * Delete a notification
     *
     * @param  int  $notificationId
     * @return void
     */
    public function delete($notificationId)
    {
        $notification = $this->getNotificationModel()::where($this->getEntityColumn(), $this->entityId)
            ->findOrFail($notificationId);

        $notification->delete();

        // Clear cache if this was an unread notification
        if ($notification->read_at === null) {
            Cache::forget("{$this->entityType}_{$this->entityId}_unread_notifications_count");
            $this->updateUnreadCount();
        }
    }

    /**
     * Update the filter
     *
     * @param  string  $filter
     * @return void
     */
    public function updatedFilter($filter)
    {
        $this->resetPage();
    }

    /**
     * Get the notification model class
     *
     * @return string
     */
    protected function getNotificationModel()
    {
        return $this->entityType === 'user' ? UserNotification::class : PetNotification::class;
    }

    /**
     * Get the entity column name
     *
     * @return string
     */
    protected function getEntityColumn()
    {
        return $this->entityType === 'user' ? 'user_id' : 'pet_id';
    }

    /**
     * Get the relationship for the sender
     *
     * @return string
     */
    protected function getSenderRelationship()
    {
        return $this->entityType === 'user' ? 'senderUser' : 'senderPet';
    }

    /**
     * Get notifications with caching
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    protected function getNotifications()
    {
        $query = $this->getNotificationModel()::where($this->getEntityColumn(), $this->entityId);

        // Apply filter
        if ($this->filter === 'unread') {
            $query->whereNull('read_at');
        } elseif ($this->filter === 'read') {
            $query->whereNotNull('read_at');
        }

        if ($this->category !== 'all') {
            $query->where('category', $this->category);
        }

        if ($this->priority !== 'all') {
            $query->where('priority', $this->priority);
        }

        // Eager load relationships
        $query->with($this->getSenderRelationship());

        // Order by created_at
        $query->orderBy('created_at', 'desc');

        // Cache the count for each filter type
        $countCacheKey = "{$this->entityType}_{$this->entityId}_notifications_count_{$this->filter}_{$this->category}_{$this->priority}";
        $count = Cache::remember($countCacheKey, now()->addMinutes(5), function () use ($query) {
            return $query->count();
        });

        // Get paginated results
        return $query->paginate(10);
    }

    /**
     * Render the component
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('livewire.common.notification-center', [
            'notifications' => $this->getNotifications(),
        ])->layout('layouts.app');
    }
}
