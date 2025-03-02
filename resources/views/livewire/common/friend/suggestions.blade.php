<div>
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-medium text-gray-800 flex items-center">
                <x-icons.users class="h-5 w-5 mr-2 text-indigo-500" stroke-width="2" />
                {{ $entityType === 'pet' ? __('friends.pet_suggestions') : __('friends.friend_suggestions') }}
            </h3>
        </div>
        <div class="p-4">
            @if($suggestions->isEmpty())
                <div class="text-center py-6">
                    <x-icons.exclamation-circle class="h-12 w-12 mx-auto text-gray-400" stroke-width="1" />
                    <p class="mt-2 text-sm text-gray-500">{{ __('friends.no_suggestions') }}</p>
                </div>
            @else
                <div class="space-y-4">
                    @foreach($suggestions as $suggestion)
                        <div class="p-3 rounded-lg border border-gray-200 hover:bg-gray-50 transition-colors duration-150">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <img src="{{ $suggestion['entity']->avatar ?? '/images/default-avatar.png' }}" 
                                         alt="{{ $suggestion['entity']->name }}" 
                                         class="h-12 w-12 rounded-full object-cover border border-gray-200" >
                                </div>
                                <div class="ml-4 flex-1">
                                    <h4 class="text-sm font-medium text-gray-900">{{ $suggestion['entity']->name }}</h4>
                                    <p class="text-xs text-gray-500 flex items-center">
                                        <x-icons.users class="h-3 w-3 mr-1" stroke-width="2" />
                                        {{ $suggestion['mutual_friends_count'] }} {{ $entityType === 'pet' ? __('friends.mutual_pet_friends') : __('friends.mutual_friends') }}
                                    </p>
                                </div>
                                <div>
                                    @livewire('common.friend.button', [
                                        'entityType' => $entityType, 
                                        'entityId' => $entity->id, 
                                        'targetId' => $suggestion['entity']->id
                                    ], key('suggestion-'.$suggestion['entity']->id))
                                </div>
                            </div>
                            @if($suggestion['mutual_friends_count'] > 0)
                                <div class="mt-2 pl-16">
                                    <p class="text-xs text-gray-600">
                                        <span class="font-medium">{{ $entityType === 'pet' ? __('friends.mutual_pet_friends') : __('friends.mutual_friends') }}:</span> 
                                        {{ collect($suggestion['mutual_friends'])->take(3)->pluck('name')->join(', ') }}
                                        @if(count($suggestion['mutual_friends']) > 3)
                                            <span class="text-indigo-600">+{{ count($suggestion['mutual_friends']) - 3 }} {{ __('more') }}</span>
                                        @endif
                                    </p>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
        <div class="px-4 py-3 bg-gray-50 text-right">
            <button 
                wire:click="loadSuggestions" 
                class="inline-flex items-center px-3 py-1.5 border border-indigo-300 text-sm leading-5 font-medium rounded-md text-indigo-700 bg-white hover:text-indigo-500 focus:outline-none focus:border-indigo-300 focus:shadow-outline-indigo active:text-indigo-800 active:bg-indigo-50 transition ease-in-out duration-150"
            >
                <x-icons.refresh class="h-4 w-4 mr-1.5" stroke-width="2" />
                {{ __('friends.refresh_suggestions') }}
            </button>
        </div>
    </div>
</div>
