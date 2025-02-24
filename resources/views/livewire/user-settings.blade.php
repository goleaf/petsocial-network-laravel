<div class="max-w-md mx-auto bg-white p-6 rounded-lg shadow">
    <h1 class="text-2xl font-bold mb-4">User Settings</h1>
    <form wire:submit.prevent="update">
        <div class="mb-4">
            <label class="block text-gray-700">Name</label>
            <input type="text" wire:model="name" class="w-full p-2 border rounded">
        </div>
        <div class="mb-4">
            <label class="block text-gray-700">Email</label>
            <input type="email" wire:model="email" class="w-full p-2 border rounded">
        </div>
        <div class="mb-4">
            <label class="block text-gray-700">New Password</label>
            <input type="password" wire:model="password" class="w-full p-2 border rounded" placeholder="Leave blank to keep current">
        </div>
        <div class="mb-4">
            <label class="block text-gray-700">Confirm Password</label>
            <input type="password" wire:model="password_confirmation" class="w-full p-2 border rounded">
        </div>
        <div class="mb-4">
            <label class="block text-gray-700">Profile Visibility</label>
            <select wire:model="profile_visibility" class="w-full p-2 border rounded">
                <option value="public">Public</option>
                <option value="friends">Friends Only</option>
                <option value="private">Private</option>
            </select>
        </div>
        <div class="mb-4">
            <label class="block text-gray-700">Posts Visibility</label>
            <select wire:model="posts_visibility" class="w-full p-2 border rounded">
                <option value="public">Public</option>
                <option value="friends">Friends Only</option>
            </select>
        </div>
        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded w-full">Save</button>
    </form>
    @if (session('message'))
        <p class="mt-4 text-green-500">{{ session('message') }}</p>
    @endif
</div>
