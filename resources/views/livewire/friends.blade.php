<div class="max-w-lg mx-auto bg-white p-6 rounded-lg shadow">
    <h1 class="text-2xl font-bold mb-4 text-center">Your Friends</h1>
    <input type="text" wire:model.debounce.500ms="search" class="w-full p-3 border rounded-lg mb-4" placeholder="Search friends...">
    @if ($friends->isEmpty())
        <p class="text-gray-500 text-center">No friends found.</p>
    @else
        <div class="mb-4 flex justify-end space-x-2">
            <button wire:click="bulkRemove" class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 {{$selectedFriends ? '' : 'opacity-50 cursor-not-allowed'}}" {{$selectedFriends ? '' : 'disabled'}}>Remove Selected</button>
            <button wire:click="$set('showCategoryModal', true)" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 {{$selectedFriends ? '' : 'opacity-50 cursor-not-allowed'}}" {{$selectedFriends ? '' : 'disabled'}}>Categorize Selected</button>
        </div>
        <ul class="grid grid-cols-1 gap-2">
            @foreach ($friends as $friend)
                <li class="flex items-center justify-between p-2 bg-gray-50 rounded-lg">
                    <div class="flex items-center">
                        <input type="checkbox" wire:model="selectedFriends" value="{{ $friend->id }}" class="mr-2">
                        <a href="{{ route('profile', $friend) }}" class="text-blue-500 hover:underline">{{ $friend->name }}</a>
                        @if ($friend->pivot->category)
                            <span class="ml-2 text-sm text-gray-500">({{ $friend->pivot->category }})</span>
                        @endif
                    </div>
                    @livewire('friend-button', ['userId' => $friend->id], key('friend-'.$friend->id))
                </li>
            @endforeach
        </ul>
        <div class="mt-4">{{ $friends->links() }}</div>
    @endif

    @if ($showCategoryModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center">
            <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md">
                <h2 class="text-xl font-semibold mb-4">Categorize Friends</h2>
                <input type="text" wire:model="category" class="w-full p-3 border rounded-lg mb-4" placeholder="e.g., Close Friends, Pet Pals">
                <div class="flex space-x-2">
                    <button wire:click="categorizeFriend" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">Save</button>
                    <button wire:click="$set('showCategoryModal', false)" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600">Cancel</button>
                </div>
            </div>
        </div>
    @endif
</div>
