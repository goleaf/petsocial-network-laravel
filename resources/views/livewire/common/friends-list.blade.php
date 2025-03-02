<div>
    <h3>{{ $title }}</h3>
    <div class="friends-list">
        @foreach($friends as $friend)
            <div class="friend-item">
                <div class="friend-avatar">
                    <img src="{{ $friend->avatar ?? '/images/default-avatar.png' }}" alt="{{ $friend->name }}">
                </div>
                <div class="friend-info">
                    <h4>{{ $friend->name }}</h4>
                    <p>{{ $friend->bio ?? '' }}</p>
                </div>
                <div class="friend-actions">
                    @livewire('common.friend-button', ['entityType' => $entityType, 'entityId' => $friend->id], key('friend-button-'.$friend->id))
                </div>
            </div>
        @endforeach
    </div>
</div>