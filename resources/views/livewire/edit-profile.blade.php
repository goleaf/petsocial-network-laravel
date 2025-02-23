<div>
    <h1>Edit Profile</h1>
    <form wire:submit.prevent="updateProfile">
        <div>
            <label>Bio</label>
            <textarea wire:model="bio"></textarea>
        </div>
        <div>
            <label>Avatar URL</label>
            <input type="text" wire:model="avatar">
        </div>
        <button type="submit">Save</button>
    </form>
    @if (session('message'))
        <p>{{ session('message') }}</p>
    @endif
</div>
