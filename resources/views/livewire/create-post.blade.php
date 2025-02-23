<div>
    @if ($editingPostId)
        <form wire:submit.prevent="update">
            <textarea wire:model="editingContent" placeholder="Edit your post..."></textarea>
            <button type="submit">Update</button>
            <button wire:click="$set('editingPostId', null)">Cancel</button>
        </form>
    @else
        <form wire:submit.prevent="save">
            <textarea wire:model="content" placeholder="What’s on your mind?"></textarea>
            <button type="submit">Post</button>
        </form>
    @endif
</div>
