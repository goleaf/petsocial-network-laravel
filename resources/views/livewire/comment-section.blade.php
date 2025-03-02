<div>
    @if ($editingCommentId)
        <form wire:submit.prevent="update">
            <textarea wire:model="editingContent" placeholder="Edit your comment..."></textarea>
            <button type="submit">Update</button>
            <button wire:click="$set('editingCommentId', null)">Cancel</button>
        </form>
    @else
        <form wire:submit.prevent="save">
            <textarea wire:model="content" placeholder="{{ $replyingToId ? 'Reply...' : 'Add a comment...' }}"></textarea>
            <button type="submit">{{ $replyingToId ? 'Reply' : 'Comment' }}</button>
            @if ($replyingToId)
                <button wire:click="$set('replyingToId', null)">Cancel Reply</button>
            @endif
        </form>
    @endif
    <div>
        @foreach ($comments as $comment)
            <div style="margin-left: 0;">
                <strong>{{ $comment->user->name }}</strong>: {!! $comment->formattedContent() !!}
                @if ($comment->user->id === auth()->id())
                    <button wire:click="edit({{ $comment->id }})">Edit</button>
                    <button wire:click="delete({{ $comment->id }})">Delete</button>
                @endif
                <button wire:click="reply({{ $comment->id }})">Reply</button>
                @livewire('content.report-comment', ['commentId' => $comment->id], key('report-comment-'.$comment->id))
                @foreach ($comment->replies as $reply)
                    <div style="margin-left: 20px;">
                        <strong>{{ $reply->user->name }}</strong>: {!! $reply->formattedContent() !!}
                        @if ($reply->user->id === auth()->id())
                            <button wire:click="edit({{ $reply->id }})">Edit</button>
                            <button wire:click="delete({{ $reply->id }})">Delete</button>
                        @endif
                        @livewire('content.report-comment', ['commentId' => $reply->id], key('report-comment-'.$reply->id))
                    </div>
                @endforeach
            </div>
        @endforeach
        {{ $comments->links() }}
    </div>
</div>
