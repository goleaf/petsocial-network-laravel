<div class="max-w-lg mx-auto bg-white p-6 rounded-lg shadow">
    <h1 class="text-2xl font-bold mb-6 text-center">{{ __('common.user_settings') }}</h1>
    <form wire:submit.prevent="update">
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold mb-2">{{ __('common.name') }}</label>
            <input type="text" wire:model="name" class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold mb-2">{{ __('common.email') }}</label>
            <input type="email" wire:model="email" class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold mb-2">{{ __('common.new_password') }}</label>
            <input type="password" wire:model="password" class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="{{ __('common.leave_blank_to_keep_current') }}">
        </div>
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold mb-2">{{ __('common.confirm_password') }}</label>
            <input type="password" wire:model="password_confirmation" class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold mb-2">{{ __('common.profile_visibility') }}</label>
            <select wire:model="profile_visibility" class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="public">{{ __('common.visibility_public') }}</option>
                <option value="friends">{{ __('common.visibility_friends') }}</option>
                <option value="private">{{ __('common.visibility_private') }}</option>
            </select>
        </div>
        <div class="mb-6">
            <label class="block text-gray-700 font-semibold mb-2">{{ __('common.posts_visibility') }}</label>
            <select wire:model="posts_visibility" class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="public">{{ __('common.visibility_public') }}</option>
                <option value="friends">{{ __('common.visibility_friends') }}</option>
            </select>
        </div>
        <button type="submit" class="w-full bg-blue-500 text-white px-4 py-3 rounded-lg hover:bg-blue-600 transition">{{ __('common.save_changes') }}</button>
    </form>
    @if (session('message'))
        <p class="mt-4 text-green-500 text-center">{{ session('message') }}</p>
    @endif
</div>
