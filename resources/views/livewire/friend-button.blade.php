<div>
    @if ($status === 'none')
        <button wire:click="sendRequest" class="text-blue-500 hover:underline">Add Friend</button>
    @elseif ($status === 'pending' && auth()->user()->pendingSentRequests()->where('receiver_id', $userId)->exists())
        <p>Request Sent</p>
    @elseif ($status === 'pending')
        <button wire:click="acceptRequest" class="text-green-500 hover:underline">Accept</button>
        <button wire:click="declineRequest" class="text-red-500 hover:underline ml-2">Decline</button>
    @elseif ($status === 'friends')
        <button wire:click="unfriend" class="text-red-500 hover:underline">Unfriend</button>
    @endif
</div>
