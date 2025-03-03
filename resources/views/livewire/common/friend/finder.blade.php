<div>
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
                <h3 class="text-lg font-medium text-gray-900">{{ $entityType === 'pet' ? __('friends.find_new_pet_friends') : __('friends.find_new_friends') }}</h3>
                <button wire:click="showImportModal" class="mt-3 sm:mt-0 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <x-icons.upload class="-ml-1 mr-2 h-5 w-5" stroke-width="2" />
                    {{ __('friends.import_contacts') }}
                </button>
            </div>
            
            <div class="mb-6">
                <div class="mt-1 relative rounded-md shadow-sm">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <x-icons.search class="h-5 w-5 text-gray-400" stroke-width="1.5" />
                    </div>
                    <input wire:model.debounce.300ms="search" type="text" class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 sm:text-sm border-gray-300 rounded-md" placeholder="{{ __('friends.search_by_info') }}">
                </div>
            </div>
            
            <div wire:loading wire:target="search" class="flex justify-center my-8">
                <x-icons.loading class="animate-spin -ml-1 mr-3 h-5 w-5 text-indigo-500" stroke-width="1.5" />
                <span>{{ __('common.searching') }}</span>
            </div>
            
            @if(count($searchResults) > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($searchResults as $result)
                        <div class="border rounded-lg p-4 hover:shadow-md transition-shadow">
                            <div class="flex items-start">
                                <div class="flex-shrink-0 h-12 w-12">
                                    @if($entityType === 'pet')
                                        <img class="h-12 w-12 rounded-full" src="{{ $result->avatar_url }}" alt="{{ $result->name }}">
                                    @else
                                        <img class="h-12 w-12 rounded-full" src="{{ $result->profile_photo_url }}" alt="{{ $result->name }}">
                                    @endif
                                </div>
                                <div class="ml-4 flex-1">
                                    <div class="flex justify-between">
                                        <a href="{{ $entityType === 'pet' ? route('pets.show', $result) : route('profile', $result) }}" class="text-sm font-medium text-gray-900 hover:underline">{{ $result->name }}</a>
                                    </div>
                                    
                                    @if($result->location)
                                        <p class="text-xs text-gray-500 mt-1">{{ $result->location }}</p>
                                    @endif
                                    
                                    <div class="mt-3">
                                        @if($entityType === 'user')
                                            @if(!$this->isSelf($result->id))
                                                @if(!$this->isFriend($result->id))
                                                    @if(!$this->hasPendingRequest($result->id))
                                                        <button wire:click="sendFriendRequest({{ $result->id }})" class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                            {{ __('friends.add_friend') }}
                                                        </button>
                                                    @else
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                            {{ __('friends.request_sent') }}
                                                        </span>
                                                    @endif
                                                @else
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                        {{ __('friends.friend') }}
                                                    </span>
                                                @endif
                                            @endif
                                        @else
                                            @if(!$this->isPetFriend($result->id))
                                                @if(!$this->hasPendingPetRequest($result->id))
                                                    <button wire:click="sendPetFriendRequest({{ $result->id }})" class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                        {{ __('friends.add_pet_friend') }}
                                                    </button>
                                                @else
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                        {{ __('friends.request_sent') }}
                                                    </span>
                                                @endif
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    {{ __('friends.pet_friend') }}
                                                </span>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @elseif(strlen($search) >= 2)
                <div class="text-center py-8 bg-gray-50 rounded-lg">
                    <x-icons.empty-results class="h-12 w-12 mx-auto text-gray-400" stroke-width="2" />
                    <p class="mt-2 text-gray-500">{{ __('friends.no_results_for') }} "{{ $search }}"</p>
                    <p class="text-sm text-gray-400">{{ __('friends.try_different_search') }}</p>
                </div>
            @endif
        </div>
    </div>
    
    <!-- Import Modal -->
    @if($showImportModal)
        <div class="fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100 sm:mx-0 sm:h-10 sm:w-10">
                                <x-icons.upload class="h-6 w-6 text-indigo-600" stroke-width="2" />
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                    {{ __('friends.import_contacts') }}
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">
                                        {{ __('friends.upload_contacts_help') }}
                                    </p>
                                </div>
                                
                                <div class="mt-4">
                                    <div class="flex items-center space-x-4">
                                        <label class="inline-flex items-center">
                                            <input type="radio" class="form-radio" wire:model="importType" value="csv">
                                            <span class="ml-2">{{ __('friends.csv') }}</span>
                                        </label>
                                        <label class="inline-flex items-center">
                                            <input type="radio" class="form-radio" wire:model="importType" value="vcf">
                                            <span class="ml-2">{{ __('friends.vcf') }}</span>
                                        </label>
                                    </div>
                                    
                                    <div class="mt-4">
                                        <label class="block text-sm font-medium text-gray-700">
                                            {{ __('friends.upload_file') }}
                                        </label>
                                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                                            <div class="space-y-1 text-center">
                                                <x-icons.upload class="mx-auto h-12 w-12 text-gray-400" stroke-width="2" />
                                                <div class="flex text-sm text-gray-600">
                                                    <label for="file-upload" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                                        <span>{{ __('friends.upload_a_file') }}</span>
                                                        <input id="file-upload" wire:model="importFile" type="file" class="sr-only">
                                                    </label>
                                                    <p class="pl-1">{{ __('friends.or_drag_drop') }}</p>
                                                </div>
                                                <p class="text-xs text-gray-500">
                                                    {{ $importType === 'csv' ? __('friends.csv') : __('friends.vcf') }} {{ __('friends.up_to_10mb') }}
                                                </p>
                                            </div>
                                        </div>
                                        @error('importFile') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                
                                @if($importResults && count($importResults) > 0)
                                    <div class="mt-4">
                                        <h4 class="text-sm font-medium text-gray-900 mb-2">{{ __('friends.import_results') }}</h4>
                                        <div class="bg-gray-50 p-3 rounded-md max-h-60 overflow-y-auto">
                                            <ul class="divide-y divide-gray-200">
                                                @foreach($importResults as $result)
                                                    <li class="py-2">
                                                        <div class="flex items-center">
                                                            <div class="flex-1">
                                                                <p class="text-sm font-medium text-gray-900">{{ $result['name'] }}</p>
                                                                <p class="text-xs text-gray-500">{{ $result['email'] }}</p>
                                                            </div>
                                                            <div>
                                                                @if($result['status'] === 'found')
                                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                                        {{ __('friends.found') }}
                                                                    </span>
                                                                @elseif($result['status'] === 'invited')
                                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                                        {{ __('friends.invited') }}
                                                                    </span>
                                                                @else
                                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                                        {{ __('friends.not_found') }}
                                                                    </span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button wire:click="processImport" wire:loading.attr="disabled" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm" {{ !$importFile ? 'disabled' : '' }}>
                            <span wire:loading.remove wire:target="processImport">{{ __('friends.import') }}</span>
                            <span wire:loading wire:target="processImport">
                                <x-icons.loading class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" stroke-width="1.5" />
                                {{ __('common.processing') }}
                            </span>
                        </button>
                        <button wire:click="closeImportModal" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            {{ __('common.cancel') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
