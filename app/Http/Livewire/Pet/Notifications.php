<?php

namespace App\Http\Livewire\Pet;

use App\Models\Pet;
use App\Models\PetNotification;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;
use Livewire\WithPagination;

class Notifications extends Component
{
    use WithPagination;
    
    public $petId;
    public $pet;
    public $unreadCount = 0;
    
    protected $paginationTheme = 'tailwind';
    
    protected $listeners = [
        'refreshNotifications' => '$refresh',
        'markAsRead' => 'markAsRead',
        'markAllAsRead' => 'markAllAsRead',
    ];
    
    /**
     * Initialize the component
     *
     * @param int $petId
     * @return void
     */
    public function mount($petId)
    {
        $this->petId = $petId;
        $this->pet = Pet::findOrFail($petId);
        
        // Check authorization
        if ($this->pet->user_id !== auth()->id()) {
            abort(403, 'You do not have permission to view notifications for this pet.');
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
        $cacheKey = "pet_{$this->petId}_unread_notifications_count";
        $this->unreadCount = Cache::remember($cacheKey, now()->addMinutes(5), function () {
            return PetNotification::where('pet_id', $this->petId)
                ->whereNull('read_at')
                ->count();
        });
    }
    
    /**
     * Mark a notification as read
     *
     * @param int $notificationId
     * @return void
     */
    public function markAsRead($notificationId)
    {
        $notification = PetNotification::where('pet_id', $this->petId)
            ->findOrFail($notificationId);
        
        $notification->markAsRead();
        
        // Clear cache
        Cache::forget("pet_{$this->petId}_unread_notifications_count");
        
        $this->updateUnreadCount();
    }
    
    /**
     * Mark all notifications as read
     *
     * @return void
     */
    public function markAllAsRead()
    {
        PetNotification::where('pet_id', $this->petId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
        
        // Clear cache
        Cache::forget("pet_{$this->petId}_unread_notifications_count");
        
        $this->unreadCount = 0;
    }
    
    /**
     * Delete a notification
     *
     * @param int $notificationId
     * @return void
     */
    public function delete($notificationId)
    {
        $notification = PetNotification::where('pet_id', $this->petId)
            ->findOrFail($notificationId);
        
        $notification->delete();
        
        // Clear cache if this was an unread notification
        if ($notification->read_at === null) {
            Cache::forget("pet_{$this->petId}_unread_notifications_count");
            $this->updateUnreadCount();
        }
    }
    
    /**
     * Render the component
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        $notifications = PetNotification::where('pet_id', $this->petId)
            ->with('senderPet') // Eager load sender pet
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        return view('livewire.pet.notifications', [
            'notifications' => $notifications,
        ])->layout('layouts.app');
    }
}
