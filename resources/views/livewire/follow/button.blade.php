<div class="flex flex-col items-end">
    <button 
        wire:click="toggleFollow" 
        class="px-3 py-1 rounded-lg text-sm {{ $isFollowing ? 'bg-gray-100 hover:bg-gray-200 text-gray-700' : 'bg-blue-500 hover:bg-blue-600 text-white' }}"
    >
        {{ $isFollowing ? 'Unfollow' : 'Follow' }} {{ $entityType === 'pet' ? $entity->name : '' }}
    </button>
    
    @if ($showNotificationToggle)
        <button 
            wire:click="toggleNotifications" 
            class="mt-1 px-3 py-1 rounded-lg text-xs {{ $notificationsEnabled ? 'bg-green-100 hover:bg-green-200 text-green-700' : 'bg-gray-100 hover:bg-gray-200 text-gray-700' }}"
        >
            {{ $notificationsEnabled ? 'Notifications On' : 'Notifications Off' }}
        </button>
    @endif
</div>
