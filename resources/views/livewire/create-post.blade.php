<div>
    @if ($editingPostId)
        <form wire:submit.prevent="update">
            <textarea wire:model="editingContent" placeholder="Edit your post..."></textarea>
            <input type="text" wire:model="tags" placeholder="Tags (comma-separated)">
            <button type="submit">Update</button>
            <button wire:click="$set('editingPostId', null)">Cancel</button>
        </form>
    @else
        <form wire:submit.prevent="save">
            <textarea wire:model="content" placeholder="What’s on your mind?"></textarea>
            <input type="text" wire:model="tags" placeholder="Tags (comma-separated)">
            <button type="submit">Post</button>
        </form>
    @endif
</div>
