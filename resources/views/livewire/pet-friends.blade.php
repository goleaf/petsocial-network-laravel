<div class="max-w-lg mx-auto bg-white p-6 rounded-lg shadow">
    <h1 class="text-2xl font-bold mb-4 text-center">{{ $pet->name }}'s Friends</h1>
    <input type="text" wire:model.debounce.500ms="search" class="w-full p-3 border rounded-lg mb-4" placeholder="Search pet friends...">
    @if ($friends->isEmpty())
        <p class="text-gray-500 text-center">No friends yet.</p>
    @else
        <div class="mb-4 flex justify-end space-x-2">
            <button wire:click="removeFriends" class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 {{$selectedFriends ? '' : 'opacity-50 cursor-not-allowed'}}" {{$selectedFriends ? '' : 'disabled'}}>Remove Selected</button>
            <button wire:click="$set('category', '')" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 {{$selectedFriends ? '' : 'opacity-50 cursor-not-allowed'}}" {{$selectedFriends ? '' : 'disabled'}}>Categorize Selected</button>
        </div>
        <ul class="grid grid-cols-1 gap-2">
            @foreach ($friends as $friend)
                <li class="flex items-center justify-between p-2 bg-gray-50 rounded-lg">
                    <div class="flex items-center">
                        <input type="checkbox" wire:model="selectedFriends" value="{{ $friend->id }}" class="mr-2">
                        <span>{{ $friend->name }} ({{ $friend->type ?? 'Unknown' }})</span>
                        @if ($friend->pivot->category)
                            <span class="ml-2 text-sm text-gray-500">({{ $friend->pivot->category }})</span>
                        @endif
                    </div>
                </li>
            @endforeach
        </ul>
        <div class="mt-4">{{ $friends->links() }}</div>
    @endif

    <h2 class="text-xl font-semibold mt-6 mb-2">Pet Friend Suggestions</h2>
    @if ($suggestions->isEmpty())
        <p class="text-gray-500 text-center">No suggestions available.</p>
    @else
        <ul class="grid grid-cols-1 gap-2">
            @foreach ($suggestions as $suggestion)
                <li class="flex items-center justify-between p-2 bg-gray-50 rounded-lg">
                    <span>{{ $suggestion->name }} ({{ $suggestion->type ?? 'Unknown' }})</span>
                    <button wire:click="addFriend({{ $suggestion->id }})" class="text-blue-500 hover:underline">Add Friend</button>
                </li>
            @endforeach
        </ul>
    @endif

    @if ($category !== null)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center">
            <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md">
                <h2 class="text-xl font-semibold mb-4">Categorize Pet Friends</h2>
                <input type="text" wire:model="category" class="w-full p-3 border rounded-lg mb-4" placeholder="e.g., Playmates, Neighbors">
                <div class="flex space-x-2">
                    <button wire:click="categorizeFriends" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">Save</button>
                    <button wire:click="$set('category', null)" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600">Cancel</button>
                </div>
            </div>
        </div>
    @endif
</div>
