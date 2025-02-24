<div class="max-w-md mx-auto bg-white p-6 rounded-lg shadow">
    <h1 class="text-2xl font-bold mb-4">Your Followers</h1>
    <input type="text" wire:model.debounce.500ms="search" class="w-full p-2 border rounded mb-4" placeholder="Search followers...">
    @if ($followers->isEmpty())
        <p class="text-gray-500">No followers found.</p>
    @else
        <ul>
            @foreach ($followers as $follower)
                <li class="flex items-center justify-between mb-2">
                    <a href="{{ route('profile', $follower) }}" class="text-blue-500 hover:underline">{{ $follower->name }}</a>
                    @livewire('follow-button', ['userId' => $follower->id], key('follow-'.$follower->id))
                </li>
            @endforeach
        </ul>
        {{ $followers->links() }}
    @endif
</div>
