{{-- Recursive renderer for nested topic branches to surface the threading tree. --}}
<ul data-testid="topic-children">
    @foreach ($topics as $threadTopic)
        <li data-topic-id="{{ $threadTopic->id }}">
            <span class="topic-title">{{ $threadTopic->title }}</span>
            <span data-testid="child-count">{{ $threadTopic->childrenRecursive->count() }}</span>
            @if ($threadTopic->childrenRecursive->isNotEmpty())
                @include('livewire.group.topics.partials.thread', ['topics' => $threadTopic->childrenRecursive])
            @endif
        </li>
    @endforeach
</ul>
