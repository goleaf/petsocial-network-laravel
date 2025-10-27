{{-- Placeholder view to support automated testing of the group topics component. --}}
<div>
    {{-- Render pinned topics to mirror the expected layout slots. --}}
    <div data-testid="pinned-topics-count">{{ $pinnedTopics->count() }}</div>

    {{-- Render regular topics to ensure pagination bindings are exercised. --}}
    <div data-testid="regular-topics-count">{{ $regularTopics->count() }}</div>
</div>
