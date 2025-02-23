<div>
    <form wire:submit.prevent="save">
        <textarea wire:model="content" placeholder="What’s on your mind?"></textarea>
        <button type="submit">Post</button>
    </form>
</div>
