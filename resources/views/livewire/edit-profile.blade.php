<div>
    <h1>Edit Profile</h1>
    <form wire:submit.prevent="updateProfile" enctype="multipart/form-data">
        <div>
            <label>Bio</label>
            <textarea wire:model="bio"></textarea>
        </div>
        <div>
            <label>Current Avatar</label>
            @if ($avatar)
                <img src="{{ Storage::url($avatar) }}" width="100">
            @else
                <p>No avatar set</p>
            @endif
        </div>
        <div>
            <label>Upload New Avatar</label>
            <input type="file" wire:model="newAvatar">
        </div>
        <button type="submit">Save</button>
    </form>
    @if (session('message'))
        <p>{{ session('message') }}</p>
    @endif
</div>
