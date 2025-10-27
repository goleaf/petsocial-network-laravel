{{-- Testing-focused scaffold that now surfaces the threaded topic hierarchy. --}}
<div>
    {{-- Render pinned topics count to verify segregation still works. --}}
    <div data-testid="pinned-topics-count">{{ $pinnedTopics->count() }}</div>

    {{-- Render regular topics count to ensure pagination bindings are exercised. --}}
    <div data-testid="regular-topics-count">{{ $regularTopics->count() }}</div>

    {{-- Surface all root topics that can be chosen as parents within the form layer. --}}
    <div data-testid="available-parent-topics">
        @foreach ($availableParentTopics as $parentCandidate)
            <span data-topic-id="{{ $parentCandidate->id }}">{{ $parentCandidate->title }}</span>
        @endforeach
    </div>

    {{-- Show the pinned topic tree so nested rendering behaviour is verifiable. --}}
    <div data-testid="pinned-topics-tree">
        @foreach ($pinnedTopics as $topic)
            <div data-topic-id="{{ $topic->id }}">
                <span class="topic-title">{{ $topic->title }}</span>
                <span data-testid="child-count">{{ $topic->childrenRecursive->count() }}</span>
                @if ($topic->childrenRecursive->isNotEmpty())
                    @include('livewire.group.topics.partials.thread', ['topics' => $topic->childrenRecursive])
                @endif
            </div>
        @endforeach
    </div>

    {{-- Show the regular topic tree so unpinned threads also expose nesting. --}}
    <div data-testid="regular-topics-tree">
        @foreach ($regularTopics as $topic)
            <div data-topic-id="{{ $topic->id }}">
                <span class="topic-title">{{ $topic->title }}</span>
                <span data-testid="child-count">{{ $topic->childrenRecursive->count() }}</span>
                @if ($topic->childrenRecursive->isNotEmpty())
                    @include('livewire.group.topics.partials.thread', ['topics' => $topic->childrenRecursive])
                @endif
            </div>
        @endforeach
    </div>
</div>
