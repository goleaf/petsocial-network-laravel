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
        <!-- Privacy controls allow the user to fine tune visibility for each profile section. -->
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-2">{{ __('common.privacy_settings') }}</h2>
            <p class="text-sm text-gray-500 mb-4">{{ __('common.privacy_settings_help') }}</p>
            <div class="space-y-4">
                @foreach($privacySections as $sectionKey => $sectionLabel)
                    <div wire:key="privacy-{{ $sectionKey }}">
                        <label class="block text-gray-700 font-semibold mb-2">{{ $sectionLabel }}</label>
                        <select wire:model="privacySettings.{{ $sectionKey }}" class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="public">{{ __('common.visibility_public') }}</option>
                            <option value="friends">{{ __('common.visibility_friends') }}</option>
                            <option value="private">{{ __('common.visibility_private') }}</option>
                        </select>
                    </div>
                @endforeach
            </div>
        </div>
        <button type="submit" class="w-full bg-blue-500 text-white px-4 py-3 rounded-lg hover:bg-blue-600 transition">{{ __('common.save_changes') }}</button>
    </form>
    @if (session('message'))
        <p class="mt-4 text-green-500 text-center">{{ session('message') }}</p>
    @endif

    <div class="mt-8 border-t border-gray-200 pt-6">
        {{-- Section dedicated to explaining and managing two-factor authentication --}}
        <h2 class="text-xl font-semibold text-center mb-3">{{ __('auth.two_factor_heading') }}</h2>
        <p class="text-sm text-gray-600 text-center mb-6">{{ __('auth.two_factor_settings_summary') }}</p>

        @if ($twoFactorEnabled)
            <div class="bg-indigo-50 border border-indigo-200 text-indigo-800 rounded-lg p-4 mb-6">
                {{-- Inform the user that two-factor authentication is currently enabled --}}
                <p>{{ __('auth.two_factor_status_enabled') }}</p>
                <a href="{{ route('two-factor.enable') }}" class="mt-3 inline-flex items-center text-sm font-semibold text-indigo-700 hover:text-indigo-900">{{ __('auth.two_factor_manage_link') }}</a>
            </div>

            <form method="POST" action="{{ route('two-factor.disable') }}" class="space-y-4">
                @csrf
                <label class="block text-left">
                    <span class="text-sm font-semibold text-gray-700">{{ __('auth.two_factor_disable_password') }}</span>
                    <input type="password" name="password" required class="mt-2 w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500" placeholder="{{ __('auth.password') }}">
                </label>
                <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-3 rounded-lg">{{ __('auth.two_factor_disable_button') }}</button>
            </form>
        @else
            <div class="bg-gray-50 border border-gray-200 text-gray-700 rounded-lg p-4 mb-6">
                {{-- Encourage the user to enable two-factor authentication when it is off --}}
                <p>{{ __('auth.two_factor_status_disabled') }}</p>
            </div>
            <a href="{{ route('two-factor.enable') }}" class="block text-center w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 rounded-lg">{{ __('auth.two_factor_enable_button') }}</a>
        @endif
    </div>
</div>
