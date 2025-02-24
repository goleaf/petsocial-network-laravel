<div class="max-w-lg mx-auto bg-white p-6 rounded-lg shadow">
    <h1 class="text-2xl font-bold mb-6 text-center">User Settings</h1>
    <form wire:submit.prevent="update">
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold mb-2">Name</label>
            <input type="text" wire:model="name" class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold mb-2">Email</label>
            <input type="email" wire:model="email" class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold mb-2">New Password</label>
            <input type="password" wire:model="password" class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Leave blank to keep current">
        </div>
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold mb-2">Confirm Password</label>
            <input type="password" wire:model="password_confirmation" class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold mb-2">Profile Visibility</label>
            <select wire:model="profile_visibility" class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="public">Public (Anyone can see)</option>
                <option value="friends">Friends Only</option>
                <option value="private">Private (Only me)</option>
            </select>
        </div>
        <div class="mb-6">
            <label class="block text-gray-700 font-semibold mb-2">Posts Visibility</label>
            <select wire:model="posts_visibility" class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="public">Public (Anyone can see)</option>
                <option value="friends">Friends Only</option>
            </select>
        </div>
        <button type="submit" class="w-full bg-blue-500 text-white px-4 py-3 rounded-lg hover:bg-blue-600 transition">Save Changes</button>
    </form>
    @if (session('message'))
        <p class="mt-4 text-green-500 text-center">{{ session('message') }}</p>
    @endif
</div>
