<div class="flex flex-col space-y-2">
    @if ($status === 'self')
        <!-- Don't show anything for own profile -->
    @elseif ($status === 'none')
        <button 
            wire:click="sendRequest" 
            class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg text-sm"
        >
            Add Friend
        </button>
    @elseif ($status === 'sent_request')
        <div class="flex space-x-2">
            <span class="text-gray-500">Request Sent</span>
            <button 
                wire:click="cancelRequest" 
                class="text-red-500 hover:text-red-600 text-sm"
            >
                Cancel
            </button>
        </div>
    @elseif ($status === 'received_request')
        <div class="flex space-x-2">
            <button 
                wire:click="acceptRequest" 
                class="px-3 py-1 bg-green-500 hover:bg-green-600 text-white rounded-lg text-sm"
            >
                Accept
            </button>
            <button 
                wire:click="declineRequest" 
                class="px-3 py-1 bg-red-500 hover:bg-red-600 text-white rounded-lg text-sm"
            >
                Decline
            </button>
        </div>
    @elseif ($status === 'friends')
        <div class="flex items-center space-x-2">
            <span class="text-green-500 font-medium">Friends</span>
            @if ($category)
                <span class="text-xs bg-gray-100 px-2 py-1 rounded-full">{{ $category }}</span>
            @endif
        </div>
        <div class="flex space-x-2">
            <button 
                wire:click="removeFriend" 
                class="px-3 py-1 bg-red-100 hover:bg-red-200 text-red-700 rounded-lg text-sm"
            >
                Remove Friend
            </button>
        </div>
    @elseif ($status === 'blocked')
        <div class="flex space-x-2">
            <span class="text-red-500">Blocked</span>
            <button 
                wire:click="unblockUser" 
                class="text-blue-500 hover:text-blue-600 text-sm"
            >
                Unblock
            </button>
        </div>
    @endif
    
    @if ($status !== 'self' && $status !== 'blocked')
        <div class="pt-2 border-t mt-2">
            <button 
                wire:click="blockUser" 
                class="text-red-500 hover:text-red-600 text-xs"
            >
                Block User
            </button>
        </div>
    @endif
</div>
