<div class="flex space-x-2">
    @if ($status === 'none')
        <button wire:click="sendRequest" class="text-blue-500 hover:underline">Add Friend</button>
    @elseif ($status === 'pending' && auth()->user()->pendingSentRequests()->where('receiver_id', $userId)->exists())
        <span class="text-gray-500">Request Sent</span>
    @elseif ($status === 'pending')
        <button wire:click="acceptRequest" class="text-green-500 hover:underline">Accept</button>
        <button wire:click="declineRequest" class="text-red-500 hover:underline">Decline</button>
    @elseif ($status === 'friends')
        <button wire:click="removeFriend" class="text-red-500 hover:underline">Remove Friend</button>
    @endif
</div>
