<div>
    @if($status === 'not_friends')
        <button wire:click="sendRequest" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
            <x-icons.user-plus class="h-4 w-4 mr-1.5" stroke-width="2" /> {{ __('friends.add_friend') }}
        </button>
    @elseif($status === 'sent_request')
        <div class="relative" x-data="{ open: false }" @click.away="open = false">
            <button 
                wire:click="toggleDropdown" 
                @click="open = !open"
                class="inline-flex items-center px-4 py-2 bg-gray-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-600 focus:bg-gray-600 active:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150"
            >
                <x-icons.clock class="h-4 w-4 mr-1.5" stroke-width="2" /> {{ __('friends.request_sent') }}
                <x-icons.chevron-down class="h-4 w-4 ml-1.5" stroke-width="2" />
            </button>
            @if($showDropdown)
                <div class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10">
                    <div class="py-1">
                        <a 
                            href="#" 
                            wire:click.prevent="cancelRequest"
                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900 flex items-center"
                        >
                            <x-icons.x class="h-4 w-4 mr-1.5 text-red-500" stroke-width="2" /> {{ __('friends.cancel_request') }}
                        </a>
                    </div>
                </div>
            @endif
        </div>
    @elseif($status === 'received_request')
        <div class="flex space-x-2">
            <button 
                wire:click="acceptRequest" 
                class="inline-flex items-center px-3 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150"
            >
                <x-icons.check class="h-4 w-4 mr-1.5" stroke-width="2" /> {{ __('friends.accept') }}
            </button>
            <button 
                wire:click="declineRequest" 
                class="inline-flex items-center px-3 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150"
            >
                <x-icons.x class="h-4 w-4 mr-1.5" stroke-width="2" /> {{ __('friends.decline') }}
            </button>
        </div>
    @elseif($status === 'friends')
        <div class="relative" x-data="{ open: false }" @click.away="open = false">
            <button 
                wire:click="toggleDropdown"
                @click="open = !open"
                class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150"
            >
                <x-icons.users class="h-4 w-4 mr-1.5" stroke-width="2" /> {{ __('friends.friend') }}
                <x-icons.chevron-down class="h-4 w-4 ml-1.5" stroke-width="2" />
            </button>
            @if($showDropdown)
                <div class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10">
                    <div class="py-1">
                        <a 
                            href="#" 
                            wire:click.prevent="removeFriendship"
                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900 flex items-center"
                        >
                            <x-icons.user-minus class="h-4 w-4 mr-1.5 text-red-500" stroke-width="2" /> {{ __('friends.remove_friend') }}
                        </a>
                        <a 
                            href="#" 
                            wire:click.prevent="blockEntity"
                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900 flex items-center"
                        >
                            <x-icons.ban class="h-4 w-4 mr-1.5 text-red-500" stroke-width="2" /> {{ __('friends.block') }}
                        </a>
                    </div>
                </div>
            @endif
        </div>
    @endif
</div>
