<div class="bg-white shadow rounded-lg p-4 space-y-4">
    <div class="flex justify-between items-center">
        <h2 class="text-xl font-semibold">
            {{ $entityType === 'user' ? 'Your Notifications' : $entity->name . '\'s Notifications' }}
            @if($unreadCount > 0)
                <span class="ml-2 px-2 py-1 text-xs bg-red-500 text-white rounded-full">{{ $unreadCount }}</span>
            @endif
        </h2>
        
        <div class="flex space-x-2">
            <select wire:model="filter" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                <option value="all">All</option>
                <option value="unread">Unread</option>
                <option value="read">Read</option>
            </select>
            
            @if($unreadCount > 0)
                <button 
                    wire:click="markAllAsRead"
                    class="px-3 py-1 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition"
                >
                    Mark all as read
                </button>
            @endif
        </div>
    </div>
    
    <div class="divide-y divide-gray-200">
        @forelse($notifications as $notification)
            <div class="py-3 {{ $notification->read_at ? 'opacity-75' : 'bg-blue-50' }}">
                <div class="flex items-start">
                    <div class="flex-shrink-0 mr-3">
                        @if($entityType === 'user' && $notification->senderUser)
                            <img src="{{ $notification->senderUser->profile_photo_url }}" alt="{{ $notification->senderUser->name }}" class="h-10 w-10 rounded-full">
                        @elseif($entityType === 'pet' && $notification->senderPet)
                            <img src="{{ $notification->senderPet->profile_photo_url }}" alt="{{ $notification->senderPet->name }}" class="h-10 w-10 rounded-full">
                        @else
                            <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center">
                                <svg class="h-6 w-6 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                                </svg>
                            </div>
                        @endif
                    </div>
                    
                    <div class="flex-1">
                        <div class="flex justify-between">
                            <p class="font-medium">
                                @if($entityType === 'user' && $notification->senderUser)
                                    {{ $notification->senderUser->name }}
                                @elseif($entityType === 'pet' && $notification->senderPet)
                                    {{ $notification->senderPet->name }}
                                @else
                                    System
                                @endif
                            </p>
                            <span class="text-sm text-gray-500">{{ $notification->created_at->diffForHumans() }}</span>
                        </div>
                        
                        <p class="text-gray-700">{{ $notification->message }}</p>
                        
                        @if($notification->action_url)
                            <a href="{{ $notification->action_url }}" class="text-sm text-blue-500 hover:underline mt-1 inline-block">
                                {{ $notification->action_text ?? 'View' }}
                            </a>
                        @endif
                    </div>
                    
                    <div class="flex-shrink-0 ml-3 space-y-1">
                        @if(!$notification->read_at)
                            <button 
                                wire:click="markAsRead({{ $notification->id }})"
                                class="text-sm text-blue-500 hover:text-blue-700 block"
                            >
                                Mark as read
                            </button>
                        @endif
                        
                        <button 
                            wire:click="delete({{ $notification->id }})"
                            class="text-sm text-red-500 hover:text-red-700 block"
                        >
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="py-4 text-center text-gray-500">
                No notifications found.
            </div>
        @endforelse
    </div>
    
    <div class="mt-4">
        {{ $notifications->links() }}
    </div>
</div>
