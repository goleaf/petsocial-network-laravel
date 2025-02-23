<div>
    @if ($editingCommentId)
        <form wire:submit.prevent="update">
            <textarea wire:model="editingContent" placeholder="Edit your comment..."></textarea>
            <button type="submit">Update</button>
            <button wire:click="$set('editingCommentId', null)">Cancel</button>
        </form>
    @else
        <form wire:submit.prevent="save">
            <textarea wire:model="content" placeholder="Add a comment..."></textarea>
            <button type="submit">Comment</button>
        </form>
    @endif
    <div>
        @foreach ($comments as $comment)
            <div>
                <p><strong>{{ $comment->user->name }}</strong>: {!! $comment->formattedContent() !!}</p>
                @if ($comment->user->id === auth()->id())
                    <button wire:click="edit({{ $comment->id }})">Edit</button>
                    <button wire:click="delete({{ $comment->id }})">Delete</button>
                @endif
                @livewire('report-comment', ['commentId' => $comment->id], key('report-comment-'.$comment->id))
            </div>
        @endforeach
        {{ $comments->links() }}
    </div>
</div>
