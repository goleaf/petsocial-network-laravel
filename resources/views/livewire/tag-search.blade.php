<div>
    <h1>Search Posts by Tag</h1>
    <input type="text" wire:model.debounce.500ms="search" placeholder="Search tags...">
    @foreach ($posts as $post)
        <div>
            <strong>{{ $post->user->name }}</strong>
            <p>{!! $post->formattedContent() !!}</p>
            @if ($post->tags->isNotEmpty())
                <p>Tags: {{ $post->tags->pluck('name')->implode(', ') }}</p>
            @endif
            <small>{{ $post->created_at->diffForHumans() }}</small>
            @livewire('content.reaction-button', ['postId' => $post->id], key('reactions-'.$post->id))
            @livewire('content.comment-section', ['postId' => $post->id], key('comments-'.$post->id))
        </div>
    @endforeach
    {{ $posts->links() }}
</div>
