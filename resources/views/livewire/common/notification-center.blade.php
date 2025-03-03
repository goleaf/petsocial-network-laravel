<div class="bg-white shadow rounded-lg p-4 space-y-4">
    <div class="flex justify-between items-center">
        <h2 class="text-xl font-semibold">
            {{ $entityType === 'user' ? __('notifications.your_notifications') : __('notifications.pet_notifications', ['name' => $entity->name]) }}
            @if($unreadCount > 0)
                <span class="ml-2 px-2 py-1 text-xs bg-red-500 text-white rounded-full">{{ __('notifications.unread_count', ['count' => $unreadCount]) }}</span>
            @endif
        </h2>
        
        <div class="flex space-x-2">
            <select wire:model="filter" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                <option value="all">{{ __('notifications.filter_all') }}</option>
                <option value="unread">{{ __('notifications.filter_unread') }}</option>
                <option value="read">{{ __('notifications.filter_read') }}</option>
            </select>
            
            @if($unreadCount > 0)
                <button 
                    wire:click="markAllAsRead"
                    class="inline-flex items-center px-3 py-1 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition"
                >
                    <x-icons.check-circle class="h-4 w-4 mr-1" stroke-width="2" />
                    {{ __('notifications.mark_all_read') }}
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
                                <x-icons.bell class="h-6 w-6 text-gray-500" stroke-width="2" />
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
                                    {{ __('notifications.system') }}
                                @endif
                            </p>
                            <span class="text-sm text-gray-500">{{ $notification->created_at->diffForHumans() }}</span>
                        </div>
                        
                        <p class="text-gray-700">{{ $notification->message }}</p>
                        
                        @if($notification->action_url)
                            <a href="{{ $notification->action_url }}" class="text-sm text-blue-500 hover:underline mt-1 inline-block">
                                {{ $notification->action_text ?? __('notifications.view') }}
                            </a>
                        @endif
                    </div>
                    
                    <div class="flex-shrink-0 ml-3 space-y-1">
                        @if(!$notification->read_at)
                            <button 
                                wire:click="markAsRead({{ $notification->id }})"
                                class="inline-flex items-center text-sm text-blue-500 hover:text-blue-700 block"
                            >
                                <x-icons.check class="h-3 w-3 mr-1" stroke-width="2" />
                                {{ __('notifications.mark_as_read') }}
                            </button>
                        @endif
                        
                        <button 
                            wire:click="delete({{ $notification->id }})"
                            class="inline-flex items-center text-sm text-red-500 hover:text-red-700 block"
                        >
                            <x-icons.trash class="h-3 w-3 mr-1" stroke-width="2" />
                            {{ __('notifications.delete') }}
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="py-4 text-center text-gray-500">
                {{ __('notifications.no_notifications') }}
            </div>
        @endforelse
    </div>
    
    <div class="mt-4">
        {{ $notifications->links() }}
    </div>
</div>
