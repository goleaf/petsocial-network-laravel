<div>
    @if ($editingCommentId)
        <form wire:submit.prevent="update">
            <textarea wire:model="editingContent" placeholder="{{ __('common.edit_your_comment') }}"></textarea>
            <button type="submit">{{ __('common.update') }}</button>
            <button wire:click="$set('editingCommentId', null)">{{ __('common.cancel') }}</button>
        </form>
    @else
        <form wire:submit.prevent="save">
            <textarea wire:model="content" placeholder="{{ $replyingToId ? __('common.reply') : __('common.add_a_comment') }}"></textarea>
            <button type="submit">{{ $replyingToId ? __('common.reply') : __('common.comment') }}</button>
            @if ($replyingToId)
                <button wire:click="$set('replyingToId', null)">{{ __('common.cancel_reply') }}</button>
            @endif
        </form>
    @endif
    <div>
        @foreach ($comments as $comment)
            <div style="margin-left: 0;">
                <strong>{{ $comment->user->name }}</strong>: {!! $comment->formattedContent() !!}
                @if ($comment->user->id === auth()->id())
                    <button wire:click="edit({{ $comment->id }})">{{ __('common.edit') }}</button>
                    <button wire:click="delete({{ $comment->id }})">{{ __('common.delete') }}</button>
                @endif
                <button wire:click="reply({{ $comment->id }})">{{ __('common.reply') }}</button>
                @livewire('content.report-comment', ['commentId' => $comment->id], key('report-comment-'.$comment->id))
                @foreach ($comment->replies as $reply)
                    <div style="margin-left: 20px;">
                        <strong>{{ $reply->user->name }}</strong>: {!! $reply->formattedContent() !!}
                        @if ($reply->user->id === auth()->id())
                            <button wire:click="edit({{ $reply->id }})">{{ __('common.edit') }}</button>
                            <button wire:click="delete({{ $reply->id }})">{{ __('common.delete') }}</button>
                        @endif
                        @livewire('content.report-comment', ['commentId' => $reply->id], key('report-comment-'.$reply->id))
                    </div>
                @endforeach
            </div>
        @endforeach
        {{ $comments->links() }}
    </div>
</div>
