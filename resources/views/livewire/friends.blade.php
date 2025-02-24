<div class="max-w-md mx-auto bg-white p-6 rounded-lg shadow">
    <h1 class="text-2xl font-bold mb-4">Your Friends</h1>
    <input type="text" wire:model.debounce.500ms="search" class="w-full p-2 border rounded mb-4" placeholder="Search friends...">
    @if ($friends->isEmpty())
        <p class="text-gray-500">No friends found.</p>
    @else
        <ul>
            @foreach ($friends as $friend)
                <li class="flex items-center justify-between mb-2">
                    <a href="{{ route('profile', $friend) }}" class="text-blue-500 hover:underline">{{ $friend->name }}</a>
                    @livewire('friend-button', ['userId' => $friend->id], key('friend-'.$friend->id))
                </li>
            @endforeach
        </ul>
        {{ $friends->links() }}
    @endif
</div>
