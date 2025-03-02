<div>
    <h2>Friend Hub</h2>
    <div class="friend-hub-container">
        <div class="friend-requests">
            <h3>Friend Requests</h3>
            @foreach($friendRequests as $request)
                <div class="friend-request-item">
                    <div class="friend-avatar">
                        <img src="{{ $request->sender->avatar ?? '/images/default-avatar.png' }}" alt="{{ $request->sender->name }}">
                    </div>
                    <div class="friend-info">
                        <h4>{{ $request->sender->name }}</h4>
                        <p>{{ $request->sender->bio ?? '' }}</p>
                    </div>
                    <div class="friend-actions">
                        <button wire:click="acceptRequest({{ $request->id }})" class="btn btn-success">Accept</button>
                        <button wire:click="declineRequest({{ $request->id }})" class="btn btn-danger">Decline</button>
                    </div>
                </div>
            @endforeach
        </div>
        
        <div class="friends-list">
            @livewire('common.friends-list', ['entityType' => $entityType, 'entityId' => $entityId], key('friends-list-'.$entityId))
        </div>
    </div>
</div>