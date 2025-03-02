<div class="max-w-4xl mx-auto">
    <div class="bg-white p-6 rounded-lg shadow mb-6">
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold">{{ $pet->name }}'s Dashboard</h1>
            
            <div class="flex space-x-2">
                <a href="{{ route('pet.friends', $pet->id) }}" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">Friends</a>
                <a href="{{ route('pet.activity', $pet->id) }}" class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600">Activities</a>
                <a href="{{ route('pet.posts', $pet->id) }}" class="px-4 py-2 bg-purple-500 text-white rounded-lg hover:bg-purple-600">Photos</a>
            </div>
        </div>
    </div>
    
    <!-- Notification Center -->
    @livewire('common.notification-center', ['entityType' => 'pet', 'entityId' => $pet->id])
 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
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
        
</div>
