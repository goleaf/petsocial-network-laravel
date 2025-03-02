<div class="max-w-md mx-auto bg-white p-6 rounded-lg shadow">
    @if (session()->has('message'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
            {{ session('message') }}
        </div>
    @endif

    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Friend Requests</h1>
        <div class="flex space-x-2">
            <button wire:click="toggleView" class="px-4 py-2 rounded-lg {{ !$showSent ? 'bg-blue-500 text-white' : 'bg-gray-200' }}">
                Received
            </button>
            <button wire:click="toggleView" class="px-4 py-2 rounded-lg {{ $showSent ? 'bg-blue-500 text-white' : 'bg-gray-200' }}">
                Sent
            </button>
        </div>
    </div>

    <input type="text" wire:model.debounce.500ms="search" class="w-full p-3 border rounded-lg mb-4" placeholder="Search by name...">

    @if ($friendships->isEmpty())
        <p class="text-gray-500 text-center py-4">No {{ $showSent ? 'sent' : 'pending' }} friend requests.</p>
    @else
        <ul class="divide-y divide-gray-200">
            @foreach ($friendships as $friendship)
                <li class="py-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center">
                                <span class="text-gray-600 font-bold">{{ substr($showSent ? $friendship->recipient->name : $friendship->sender->name, 0, 1) }}</span>
                            </div>
                            <a href="{{ route('profile', $showSent ? $friendship->recipient : $friendship->sender) }}" class="text-blue-500 hover:underline font-medium">
                                {{ $showSent ? $friendship->recipient->name : $friendship->sender->name }}
                            </a>
                        </div>
                        <div class="flex space-x-2">
                            @if ($showSent)
                                <button wire:click="cancelRequest({{ $friendship->id }})" class="px-3 py-1 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg text-sm">
                                    Cancel
                                </button>
                            @else
                                <button wire:click="accept({{ $friendship->id }})" class="px-3 py-1 bg-green-500 hover:bg-green-600 text-white rounded-lg text-sm">
                                    Accept
                                </button>
                                <button wire:click="decline({{ $friendship->id }})" class="px-3 py-1 bg-red-500 hover:bg-red-600 text-white rounded-lg text-sm">
                                    Decline
                                </button>
                            @endif
                        </div>
                    </div>
                </li>
            @endforeach
        </ul>

        <div class="mt-4">
            {{ $friendships->links() }}
        </div>
    @endif
</div>
