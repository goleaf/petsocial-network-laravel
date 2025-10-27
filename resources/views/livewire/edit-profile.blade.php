<div class="max-w-3xl mx-auto bg-white dark:bg-gray-900 p-6 rounded-lg shadow space-y-8">
    <!-- Heading reminding the user about the customization context -->
    <div class="text-center">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ __('profile.edit_profile') }}</h1>
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ __('profile.edit_profile_helper') }}</p>
    </div>

    <!-- Cover photo management section -->
    <section class="space-y-4">
        <header>
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('profile.cover_photo') }}</h2>
            <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('profile.cover_photo_helper') }}</p>
        </header>
        <div class="relative h-48 rounded-xl overflow-hidden border border-dashed border-gray-300 dark:border-gray-700 flex items-center justify-center bg-gray-100 dark:bg-gray-800">
            @if ($newCoverPhoto)
                <img src="{{ $newCoverPhoto->temporaryUrl() }}" alt="{{ __('profile.cover_photo') }}" class="object-cover w-full h-full">
            @elseif ($coverPhoto)
                <img src="{{ Storage::url($coverPhoto) }}" alt="{{ __('profile.cover_photo') }}" class="object-cover w-full h-full">
            @else
                <span class="text-gray-500 dark:text-gray-400">{{ __('profile.cover_photo_empty') }}</span>
            @endif
            <div wire:loading.flex wire:target="newCoverPhoto" class="absolute inset-0 bg-black bg-opacity-60 text-white items-center justify-center">
                <span class="animate-pulse">{{ __('profile.uploading') }}</span>
            </div>
        </div>
        <input type="file" wire:model="newCoverPhoto" class="w-full text-sm text-gray-900 dark:text-gray-100 border border-gray-300 dark:border-gray-700 rounded-lg cursor-pointer focus:outline-none focus:ring-2 focus:ring-blue-500">
    </section>

    <!-- Avatar management section -->
    <section class="space-y-4">
        <header>
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('profile.profile_photo') }}</h2>
            <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('profile.profile_photo_helper') }}</p>
        </header>
        <div class="flex flex-col sm:flex-row items-center sm:items-end gap-4">
            <div class="relative">
                @if ($newAvatar)
                    <img src="{{ $newAvatar->temporaryUrl() }}" class="w-32 h-32 rounded-full object-cover border-4 border-white shadow" alt="{{ __('profile.profile_photo') }}">
                @elseif ($avatar)
                    <img src="{{ Storage::url($avatar) }}" class="w-32 h-32 rounded-full object-cover border-4 border-white shadow" alt="{{ __('profile.profile_photo') }}">
                @else
                    <div class="w-32 h-32 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center text-gray-500 dark:text-gray-300">{{ __('profile.no_photo') }}</div>
                @endif
                <div wire:loading.flex wire:target="newAvatar" class="absolute inset-0 bg-black bg-opacity-60 rounded-full text-white items-center justify-center">
                    <span class="animate-pulse">{{ __('profile.uploading') }}</span>
                </div>
            </div>
            <input type="file" wire:model="newAvatar" class="w-full sm:w-auto text-sm text-gray-900 dark:text-gray-100 border border-gray-300 dark:border-gray-700 rounded-lg cursor-pointer focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
    </section>

    <!-- Biography and location form fields -->
    <section class="grid grid-cols-1 gap-6">
        <div>
            <label class="block text-gray-700 dark:text-gray-300 font-semibold mb-2">{{ __('profile.bio') }}</label>
            <textarea wire:model.defer="bio" class="w-full p-3 border border-gray-300 dark:border-gray-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100" rows="5" placeholder="{{ __('profile.bio_placeholder') }}"></textarea>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('profile.bio_helper') }}</p>
        </div>
        <div>
            <label class="block text-gray-700 dark:text-gray-300 font-semibold mb-2">{{ __('profile.location') }}</label>
            <input type="text" wire:model.defer="location" class="w-full p-3 border border-gray-300 dark:border-gray-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100" placeholder="{{ __('profile.location_placeholder') }}">
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('profile.location_helper') }}</p>
        </div>
    </section>

    <!-- Submission CTA -->
    <div class="pt-4 border-t border-gray-200 dark:border-gray-700 flex flex-col sm:flex-row items-center sm:justify-between gap-4">
        <button type="button" wire:click="updateProfile" class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold transition focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
            {{ __('profile.save_changes') }}
        </button>
        @if (session('message'))
            <p class="text-sm text-green-600 dark:text-green-400">{{ session('message') }}</p>
        @endif
    </div>
</div>
