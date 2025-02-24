<div class="max-w-md mx-auto bg-white p-6 rounded-lg shadow">
    <h1 class="text-2xl font-bold mb-4">Friend Requests</h1>
    @if ($pendingRequests->isEmpty())
        <p class="text-gray-500">No pending friend requests.</p>
    @else
        <ul>
            @foreach ($pendingRequests as $request)
                <li class="flex items-center justify-between mb-2">
                    <a href="{{ route('profile', $request->sender) }}" class="text-blue-500 hover:underline">{{ $request->sender->name }}</a>
                    <div>
                        <button wire:click="accept({{ $request->id }})" class="text-green-500 hover:underline">Accept</button>
                        <button wire:click="decline({{ $request->id }})" class="text-red-500 hover:underline ml-2">Decline</button>
                    </div>
                </li>
            @endforeach
        </ul>
    @endif
</div>
