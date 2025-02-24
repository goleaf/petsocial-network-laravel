<div class="max-w-lg mx-auto bg-white p-6 rounded-lg shadow">
    <h1 class="text-2xl font-bold mb-4 text-center">Your Friends</h1>
    <input type="text" wire:model.debounce.500ms="search" class="w-full p-3 border rounded-lg mb-4" placeholder="Search friends...">
    @if ($friends->isEmpty())
        <p class="text-gray-500 text-center">No friends found.</p>
    @else
        <ul class="grid grid-cols-1 gap-2">
            @foreach ($friends as $friend)
                <li class="flex items-center justify-between">
                    <a href="{{ route('profile', $friend) }}" class="text-blue-500 hover:underline">{{ $friend->name }}</a>
                    @livewire('friend-button', ['userId' => $friend->id], key('friend-'.$friend->id))
                </li>
            @endforeach
        </ul>
        <div class="mt-4">{{ $friends->links() }}</div>
    @endif
</div>
