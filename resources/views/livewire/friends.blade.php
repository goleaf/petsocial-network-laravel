<div class="max-w-lg mx-auto bg-white p-6 rounded-lg shadow">
    @if (session()->has('message'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
            {{ session('error') }}
        </div>
    @endif

    <h1 class="text-2xl font-bold mb-4 text-center">{{ __('friendships.my_friends') }}</h1>
    
    <div class="mb-4">
        <input type="text" wire:model.debounce.500ms="search" class="w-full p-3 border rounded-lg mb-4" placeholder="{{ __('friendships.search_friends') }}">
        
        @if ($categories->count() > 0)
            <div class="flex flex-wrap gap-2 mb-4">
                <button wire:click="setFilterCategory('')" class="px-3 py-1 rounded-full text-sm {{ $filterCategory === '' ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-700' }}">
                    {{ __('friendships.all_friends') }}
                </button>
                @foreach ($categories as $cat)
                    <button wire:click="setFilterCategory('{{ $cat }}')" class="px-3 py-1 rounded-full text-sm {{ $filterCategory === $cat ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-700' }}">
                        {{ $cat }}
                    </button>
                @endforeach
            </div>
        @endif
    </div>
    
    @if ($friends->isEmpty())
        <p class="text-gray-500 text-center py-4">{{ __('friendships.no_friends') }}</p>
    @else
        <div class="mb-4 flex justify-end space-x-2">
            <button wire:click="bulkRemove" class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 {{ count($selectedFriends) > 0 ? '' : 'opacity-50 cursor-not-allowed' }}" {{ count($selectedFriends) > 0 ? '' : 'disabled' }}>
                {{ __('friendships.remove_selected') }}
            </button>
            <button wire:click="$set('showCategoryModal', true)" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 {{ count($selectedFriends) > 0 ? '' : 'opacity-50 cursor-not-allowed' }}" {{ count($selectedFriends) > 0 ? '' : 'disabled' }}>
                {{ __('friendships.categorize_selected') }}
            </button>
        </div>
        
        <ul class="divide-y divide-gray-200">
            @foreach ($friends as $friend)
                <li class="py-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <input type="checkbox" wire:model="selectedFriends" value="{{ $friend->id }}" class="h-4 w-4 text-blue-600">
                            <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center">
                                <span class="text-gray-600 font-bold">{{ substr($friend->name, 0, 1) }}</span>
                            </div>
                            <div>
                                <a href="{{ route('profile', $friend) }}" class="text-blue-500 hover:underline font-medium">{{ $friend->name }}</a>
                                @if ($friend->pivot && $friend->pivot->category)
                                    <span class="ml-2 text-xs px-2 py-1 bg-gray-100 text-gray-600 rounded-full">{{ $friend->pivot->category }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="flex space-x-2">
                            <button wire:click="removeFriend({{ $friend->id }})" class="px-3 py-1 bg-red-100 hover:bg-red-200 text-red-700 rounded-lg text-sm">
                                {{ __('friendships.remove_friend') }}
                            </button>
                        </div>
                    </div>
                </li>
            @endforeach
        </ul>
        
        <div class="mt-4">
            {{ $friends->links() }}
        </div>
    @endif

    @if ($showCategoryModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md">
                <h2 class="text-xl font-semibold mb-4">{{ __('friendships.categorize_friends') }}</h2>
                <input type="text" wire:model="category" class="w-full p-3 border rounded-lg mb-4" placeholder="{{ __('friendships.category_placeholder') }}">
                <div class="flex justify-end space-x-2">
                    <button wire:click="$set('showCategoryModal', false)" class="px-4 py-2 bg-gray-200 rounded-lg">
                        {{ __('friendships.cancel') }}
                    </button>
                    <button wire:click="categorizeFriends" class="px-4 py-2 bg-blue-500 text-white rounded-lg">
                        {{ __('friendships.save') }}
                    </button>
                </div>
            </div>
        </div>
    @endif
    
    @if (!empty($recommendations))
        <div class="mt-8 p-4 bg-blue-50 rounded-lg">
            <h2 class="text-lg font-semibold mb-3">{{ __('friendships.suggestions') }}</h2>
            <ul class="divide-y divide-blue-100">
                @foreach ($recommendations as $recommendation)
                    <li class="py-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center">
                                    <span class="text-gray-600 font-bold">{{ substr($recommendation['user']->name, 0, 1) }}</span>
                                </div>
                                <div>
                                    <a href="{{ route('profile', $recommendation['user']) }}" class="text-blue-500 hover:underline font-medium">{{ $recommendation['user']->name }}</a>
                                    <div class="text-xs text-gray-500">{{ $recommendation['mutual_count'] }} {{ trans_choice('friendships.mutual_friends_count', $recommendation['mutual_count']) }}</div>
                                </div>
                            </div>
                            <a href="{{ route('friendships.request', $recommendation['user']) }}" class="px-3 py-1 bg-blue-500 hover:bg-blue-600 text-white rounded-lg text-sm">
                                {{ __('friendships.add_friend') }}
                            </a>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
</div>
