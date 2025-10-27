{{-- Minimal harness view for exercising the Finder component in tests. --}}
<div>
    {{-- Surface the total search results so assertions can inspect derived data. --}}
    <span class="finder-harness-count">{{ $searchResults->count() }}</span>
</div>
