<div>
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
            <div class="flex flex-col sm:flex-row justify-between items-center space-y-2 sm:space-y-0">
                <h3 class="text-lg font-medium text-gray-800 flex items-center">
                    <x-icons.users class="h-5 w-5 mr-2 text-indigo-500" stroke-width="2" />
                    {{ $entityType === 'pet' ? __('friends.pet_friends') : __('friends.friends') }}
                </h3>
                <div class="relative w-full sm:w-64">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <x-icons.search class="h-5 w-5 text-gray-400" stroke-width="2" />
                    </div>
                    <input 
                        type="text" 
                        wire:model.debounce.300ms="search" 
                        class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" 
                        placeholder="{{ $entityType === 'pet' ? __('friends.search_pet_friends') : __('friends.search_friends') }}"
                    >
                </div>
            </div>
        </div>
        <div class="p-4">
            <div class="mb-4">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-3 sm:space-y-0">
                    <div class="w-full sm:w-auto">
                        <select 
                            wire:model="categoryFilter" 
                            class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                        >
                            <option value="">{{ __('friends.all_categories') }}</option>
                            @foreach($categories as $category)
                                <option value="{{ $category }}">{{ $category }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-center space-x-4">
                        <label class="inline-flex items-center">
                            <input 
                                type="checkbox" 
                                wire:model="selectAll" 
                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                            >
                            <span class="ml-2 text-sm text-gray-700">{{ __('friends.select_all') }}</span>
                        </label>
                        <div class="flex space-x-2">
                            <button 
                                wire:click="showCategoryModal" 
                                class="inline-flex items-center px-3 py-1.5 border border-indigo-300 text-sm leading-5 font-medium rounded-md text-indigo-700 bg-white hover:text-indigo-500 focus:outline-none focus:border-indigo-300 focus:shadow-outline-indigo active:text-indigo-800 active:bg-indigo-50 transition ease-in-out duration-150 {{ empty($selectedFriends) ? 'opacity-50 cursor-not-allowed' : '' }}"
                                {{ empty($selectedFriends) ? 'disabled' : '' }}
                            >
                                <x-icons.tag class="h-4 w-4 mr-1.5" stroke-width="2" />
                                {{ __('friends.categorize') }}
                            </button>
                            <button 
                                wire:click="removeFriends" 
                                class="inline-flex items-center px-3 py-1.5 border border-red-300 text-sm leading-5 font-medium rounded-md text-red-700 bg-white hover:text-red-500 focus:outline-none focus:border-red-300 focus:shadow-outline-red active:text-red-800 active:bg-red-50 transition ease-in-out duration-150 {{ empty($selectedFriends) ? 'opacity-50 cursor-not-allowed' : '' }}"
                                {{ empty($selectedFriends) ? 'disabled' : '' }}
                            >
                                <x-icons.trash class="h-4 w-4 mr-1.5" stroke-width="2" />
                                {{ __('friends.remove') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            @if($friends->isEmpty())
                <div class="text-center py-8">
                    <x-icons.exclamation-circle class="h-12 w-12 mx-auto text-gray-400" stroke-width="1" />
                    <p class="mt-2 text-sm text-gray-500">{{ __('friends.no_friends_found') }} {{ $search ? __('friends.try_different_search') : '' }}</p>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($friends as $friend)
                        <div class="border rounded-lg overflow-hidden {{ in_array($friend->id, $selectedFriends) ? 'ring-2 ring-indigo-500' : 'hover:shadow-md' }} transition-all duration-150">
                            <div class="p-3 bg-gray-50 border-b flex items-center space-x-3">
                                <div>
                                    <input 
                                        type="checkbox" 
                                        wire:model="selectedFriends" 
                                        value="{{ $friend->id }}" 
                                        id="friend-{{ $friend->id }}"
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                    >
                                </div>
                                <div class="flex-shrink-0">
                                    <img 
                                        src="{{ $friend->avatar ?? '/images/default-avatar.png' }}" 
                                        alt="{{ $friend->name }}" 
                                        class="h-10 w-10 rounded-full object-cover border border-gray-200"
                                    >
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-sm font-medium text-gray-900 truncate">{{ $friend->name }}</h4>
                                    <p class="text-xs text-gray-500 truncate">@{{ $friend->username }}</p>
                                </div>
                            </div>
                            <div class="p-3">
                                @if($friend->pivot && $friend->pivot->category)
                                    <div class="mb-2">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                            <x-icons.tag class="h-3 w-3 mr-1" stroke-width="2" />
                                            {{ $friend->pivot->category }}
                                        </span>
                                    </div>
                                @endif
                                <div class="mt-2">
                                    @livewire('common.friend.button', [
                                        'entityType' => $entityType, 
                                        'entityId' => $entity->id, 
                                        'targetId' => $friend->id
                                    ], key('friend-button-'.$friend->id))
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <div class="mt-6">
                    {{ $friends->links() }}
                </div>
            @endif
        </div>
    </div>
    
    @if($showCategoryModal)
        <div class="fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100 sm:mx-0 sm:h-10 sm:w-10">
                                <x-icons.tag class="h-6 w-6 text-indigo-600" stroke-width="2" />
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                    {{ __('friends.apply_category') }}
                                </h3>
                                <div class="mt-4">
                                    <label for="newCategory" class="block text-sm font-medium text-gray-700">{{ __('friends.category_name') }}</label>
                                    <input 
                                        type="text" 
                                        wire:model="newCategory" 
                                        id="newCategory" 
                                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" 
                                        placeholder="Enter category name or leave empty to remove category"
                                    >
                                </div>
                                <p class="mt-2 text-sm text-gray-500">
                                    {{ trans_choice('friends.category_apply_info', count($selectedFriends), ['count' => count($selectedFriends)]) }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button 
                            type="button" 
                            wire:click="applyCategory" 
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm"
                        >
                            {{ __('friends.apply') }}
                        </button>
                        <button 
                            type="button" 
                            wire:click="cancelCategoryModal" 
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                        >
                            {{ __('friends.cancel') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
