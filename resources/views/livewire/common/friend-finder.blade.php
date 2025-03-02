<div>
    <h2>Find Friends</h2>
    <div class="search-container">
        <input type="text" wire:model.debounce.300ms="searchTerm" placeholder="Search for friends...">
        <button wire:click="search" class="btn btn-primary">Search</button>
    </div>
    
    <div class="search-results">
        @foreach($searchResults as $result)
            <div class="result-item">
                <div class="result-avatar">
                    <img src="{{ $result->avatar ?? '/images/default-avatar.png' }}" alt="{{ $result->name }}">
                </div>
                <div class="result-info">
                    <h4>{{ $result->name }}</h4>
                    <p>{{ $result->bio ?? '' }}</p>
                </div>
                <div class="result-actions">
                    @livewire('common.friend-button', ['entityType' => $entityType, 'entityId' => $result->id], key('result-button-'.$result->id))
                </div>
            </div>
        @endforeach
    </div>
</div>