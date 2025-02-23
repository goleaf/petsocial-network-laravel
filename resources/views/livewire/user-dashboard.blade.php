<div>
    <h1>Welcome, {{ auth()->user()->name }}!</h1>
    @livewire('create-post')
    <h2>Recent Posts</h2>
    @foreach ($posts as $post)
        <div>
            <strong>{{ $post->user->name }}</strong>
            @if ($post->user->id !== auth()->id())
                @livewire('follow-button', ['userId' => $post->user->id], key('follow-'.$post->id))
            @endif
            <p>{!! $post->formattedContent() !!}</p>
            @if ($post->tags->isNotEmpty())
                <p>Tags: {{ $post->tags->pluck('name')->implode(', ') }}</p>
            @endif
            <small>{{ $post->created_at->diffForHumans() }}</small>
            @if ($post->user->id === auth()->id())
                <button wire:click="$emit('edit', {{ $post->id }})">Edit</button>
                <button wire:click="$emit('delete', {{ $post->id }})">Delete</button>
            @endif
            @livewire('reaction-button', ['postId' => $post->id], key('reactions-'.$post->id))
            @livewire('comment-section', ['postId' => $post->id], key('comments-'.$post->id))
            @livewire('report-post', ['postId' => $post->id], key('report-'.$post->id))
        </div>
    @endforeach
    {{ $posts->links() }}
</div>
