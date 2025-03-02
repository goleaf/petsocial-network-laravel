<div class="flex flex-col items-end">
    <button 
        wire:click="{{ $isFollowing ? 'unfollow' : 'follow' }}" 
        class="inline-flex items-center px-3 py-1 rounded-lg text-sm font-medium transition-colors duration-150 ease-in-out {{ $isFollowing ? 'bg-gray-100 hover:bg-gray-200 text-gray-700' : 'bg-blue-500 hover:bg-blue-600 text-white' }}"
    >
        @if ($isFollowing)
            <x-icons.user-minus class="h-4 w-4 mr-1" stroke-width="2" />
            Unfollow
        @else
            <x-icons.user-plus class="h-4 w-4 mr-1" stroke-width="2" />
            Follow
        @endif
        {{ $entityType === 'pet' ? $entity->name : '' }}
    </button>
    
    @if ($isFollowing)
        <button 
            wire:click="toggleNotifications" 
            class="mt-1 inline-flex items-center px-3 py-1 rounded-lg text-xs font-medium transition-colors duration-150 ease-in-out {{ $isReceivingNotifications ? 'bg-green-100 hover:bg-green-200 text-green-700' : 'bg-gray-100 hover:bg-gray-200 text-gray-700' }}"
        >
            @if ($isReceivingNotifications)
                <x-icons.bell class="h-3 w-3 mr-1" stroke-width="2" />
                Notifications On
            @else
                <x-icons.bell-slash class="h-3 w-3 mr-1" stroke-width="2" />
                Notifications Off
            @endif
        </button>
    @endif
</div>
