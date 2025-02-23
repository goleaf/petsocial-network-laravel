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
            <p>{{ $post->content }}</p>
            <small>{{ $post->created_at->diffForHumans() }}</small>
            @livewire('like-button', ['postId' => $post->id], key('likes-'.$post->id))
            @livewire('comment-section', ['postId' => $post->id], key('comments-'.$post->id))
        </div>
    @endforeach
</div>
