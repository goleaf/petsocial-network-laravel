<div>
    <form wire:submit.prevent="save">
        <textarea wire:model="content" placeholder="Add a comment..."></textarea>
        <button type="submit">Comment</button>
    </form>
    <div>
        @foreach ($comments as $comment)
            <p><strong>{{ $comment->user->name }}</strong>: {{ $comment->content }}</p>
        @endforeach
        {{ $comments->links() }}
    </div>
</div>
