<div class="max-w-4xl mx-auto">
    <div class="bg-white p-6 rounded-lg shadow mb-6">
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold">{{ __('notifications.pet_dashboard', ['name' => $pet->name]) }}</h1>
            
            <div class="flex space-x-2">
                <a href="{{ route('pet.friends', $pet->id) }}" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 flex items-center">
                    <x-icons.friends class="h-5 w-5 mr-1" />
                    {{ __('notifications.friends') }}
                </a>
                <a href="{{ route('activity', ['entity_type' => 'pet', 'entity_id' => $pet->id]) }}" class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 flex items-center">
                    <x-icons.activity class="h-5 w-5 mr-1" />
                    {{ __('notifications.activities') }}
                </a>
                <a href="{{ route('pet.posts', $pet->id) }}" class="px-4 py-2 bg-purple-500 text-white rounded-lg hover:bg-purple-600 flex items-center">
                    <x-icons.photos class="h-5 w-5 mr-1" />
                    {{ __('notifications.photos') }}
                </a>
            </div>
        </div>
    </div>
    
    <!-- Notification Center -->
    @livewire('common.notification-center', ['entityType' => 'pet', 'entityId' => $pet->id])
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
                                        href="{{ route('activity', ['entity_type' => 'pet', 'entity_id' => $notification->sender_pet_id]) }}?activityId={{ $notification->data['activity_id'] }}" 
                                        class="text-blue-500 hover:underline text-sm"
                                    >
                                        {{ __('notifications.view_activity') }}
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </li>
            @endforeach
        </ul>
        
</div>
