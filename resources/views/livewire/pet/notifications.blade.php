<div class="max-w-4xl mx-auto bg-white p-6 rounded-lg shadow">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">{{ $pet->name }}'s Notifications</h1>
        
        @if ($unreadCount > 0)
            <button 
                wire:click="markAllAsRead" 
                class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600"
            >
                Mark All as Read
            </button>
        @endif
    </div>
    
    @if (session()->has('message'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
            {{ session('message') }}
        </div>
    @endif
    
    @if ($notifications->isEmpty())
        <div class="text-center py-8">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
            <p class="mt-4 text-lg text-gray-600">No notifications yet!</p>
            <p class="text-gray-500">When your friends add you or log new activities, you'll see them here.</p>
        </div>
    @else
        <ul class="divide-y divide-gray-200">
            @foreach ($notifications as $notification)
                <li class="py-4 {{ $notification->read_at ? 'bg-white' : 'bg-blue-50' }} rounded-lg transition-colors duration-200">
                    <div class="flex items-start">
                        <div class="mr-4">
                            @if ($notification->sender_pet_id && $notification->senderPet)
                                <img src="{{ $notification->senderPet->avatar_url }}" alt="{{ $notification->senderPet->name }}" class="w-12 h-12 rounded-full object-cover">
                            @else
                                <div class="w-12 h-12 rounded-full bg-gray-200 flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                    </svg>
                                </div>
                            @endif
                        </div>
                        <div class="flex-1">
                            <div class="flex justify-between">
                                <div>
                                    <p class="text-gray-800">
                                        @if ($notification->sender_pet_id && $notification->senderPet)
                                            <a href="{{ route('pet.profile', $notification->sender_pet_id) }}" class="font-medium hover:underline">
                                                {{ $notification->senderPet->name }}
                                            </a>
                                        @else
                                            System
                                        @endif
                                        <span>{{ $notification->content }}</span>
                                    </p>
                                    <p class="text-sm text-gray-500 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                                </div>
                                <div class="flex space-x-2">
                                    @if (!$notification->read_at)
                                        <button 
                                            wire:click="markAsRead({{ $notification->id }})" 
                                            class="text-blue-500 hover:underline text-sm"
                                        >
                                            Mark as read
                                        </button>
                                    @endif
                                    <button 
                                        wire:click="delete({{ $notification->id }})" 
                                        class="text-red-500 hover:underline text-sm"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </div>
                            
                            @if ($notification->type === 'friend_request' && $notification->sender_pet_id)
                                <div class="mt-2">
                                    @livewire('common.friend.button', [
                                        'entityType' => 'pet', 
                                        'entityId' => $pet->id, 
                                        'targetId' => $notification->sender_pet_id
                                    ], key('friend-request-'.$notification->id))
                                </div>
                            @endif
                            
                            @if ($notification->type === 'activity' && isset($notification->data['activity_id']))
                                <div class="mt-2">
                                    <a 
                                        href="{{ route('pet.activity', ['petId' => $notification->sender_pet_id]) }}?activityId={{ $notification->data['activity_id'] }}" 
                                        class="text-blue-500 hover:underline text-sm"
                                    >
                                        View Activity
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </li>
            @endforeach
        </ul>
        
        <div class="mt-6">
            {{ $notifications->links() }}
        </div>
    @endif
</div>
