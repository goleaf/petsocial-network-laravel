<div class="max-w-lg mx-auto bg-white p-6 rounded-lg shadow">
    <h1 class="text-2xl font-bold mb-6 text-center">Edit Profile</h1>
    <form wire:submit.prevent="updateProfile" enctype="multipart/form-data">
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold mb-2">Bio</label>
            <textarea wire:model="bio" class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" rows="4" placeholder="Tell us about yourself or your pet..."></textarea>
        </div>
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold mb-2">Location</label>
            <input type="text" wire:model="location" class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="e.g., New York, NY">
        </div>
        <div class="mb-6">
            <label class="block text-gray-700 font-semibold mb-2">Profile Photo</label>
            @if ($avatar)
                <img src="{{ Storage::url($avatar) }}" class="w-24 h-24 rounded-full mx-auto mb-3">
            @else
                <div class="w-24 h-24 rounded-full bg-gray-200 mx-auto mb-3 flex items-center justify-center text-gray-500">No Photo</div>
            @endif
            <input type="file" wire:model="newAvatar" class="w-full p-2 border rounded-lg">
        </div>
        <button type="submit" class="w-full bg-blue-500 text-white px-4 py-3 rounded-lg hover:bg-blue-600 transition">Save Changes</button>
    </form>
    @if (session('message'))
        <p class="mt-4 text-green-500 text-center">{{ session('message') }}</p>
    @endif
</div>
