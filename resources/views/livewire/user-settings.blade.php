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
            {{-- Audience presets allow members to update every section in one action. --}}
            <div class="flex flex-wrap gap-2 mb-3" role="group" aria-label="{{ __('common.privacy_presets') }}">
                @foreach($privacyPresets as $presetKey => $presetLabel)
                    <button
                        type="button"
                        wire:click="applyPrivacyPreset('{{ $presetKey }}')"
                        wire:loading.attr="disabled"
                        class="px-3 py-2 text-sm font-medium rounded-md border border-gray-200 bg-gray-50 text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-400"
                    >
                        {{ $presetLabel }}
                    </button>
                @endforeach
            </div>
            <p class="text-xs text-gray-400 mb-4">{{ __('common.privacy_presets_help') }}</p>
            @if ($privacyPresetNotice)
                <p class="text-xs text-green-600 mb-4">{{ $privacyPresetNotice }}</p>
            @endif
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
        <div class="mt-8">
            {{-- Notification preferences allow members to tailor delivery channels and cadences. --}}
            <h2 class="text-lg font-semibold text-gray-800 mb-2">{{ __('notifications.notification_settings_heading') }}</h2>
            <p class="text-sm text-gray-500 mb-4">{{ __('notifications.notification_settings_description') }}</p>

            {{-- Channel toggles --}}
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">{{ __('notifications.notification_channels') }}</h3>
                @php($channelLabels = [
                    'in_app' => __('notifications.notification_channel_in_app'),
                    'email' => __('notifications.notification_channel_email'),
                    'push' => __('notifications.notification_channel_push'),
                ])
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    @foreach($channelLabels as $channelKey => $channelLabel)
                        <label class="flex items-center space-x-3 bg-white border border-gray-200 rounded-md px-3 py-2" wire:key="channel-{{ $channelKey }}">
                            <input type="checkbox" wire:model="notificationPreferences.channels.{{ $channelKey }}" class="h-4 w-4 text-blue-600 rounded">
                            <span class="text-sm text-gray-700">{{ $channelLabel }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- Priority frequency controls ensure urgent alerts stay instant while others can be throttled. --}}
            <div class="bg-white border border-gray-200 rounded-lg p-4 mb-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-2">{{ __('notifications.notification_priority_frequencies') }}</h3>
                <p class="text-xs text-gray-500 mb-4">{{ __('notifications.notification_frequency_help') }}</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @foreach($notificationPriorities as $priority)
                        <label class="block" wire:key="priority-{{ $priority }}">
                            <span class="block text-xs font-semibold text-gray-600 uppercase mb-1">{{ ucfirst($priority) }}</span>
                            <select wire:model="notificationPreferences.frequency.{{ $priority }}" class="w-full p-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                @foreach($notificationFrequencies as $frequencyKey => $frequencyLabel)
                                    <option value="{{ $frequencyKey }}">{{ $frequencyLabel }}</option>
                                @endforeach
                            </select>
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- Per-category controls --}}
            <div class="space-y-4 mb-6">
                <h3 class="text-sm font-semibold text-gray-700">{{ __('notifications.notification_categories') }}</h3>
                @foreach($notificationCategories as $categoryKey => $categoryDefinition)
                    @continue($categoryKey === 'digest')
                    <div class="border border-gray-200 rounded-lg p-4" wire:key="category-{{ $categoryKey }}">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-sm font-semibold text-gray-800">{{ $categoryDefinition['label'] ?? ucfirst(str_replace('_', ' ', $categoryKey)) }}</p>
                                <p class="text-xs text-gray-500">{{ $categoryDefinition['description'] ?? '' }}</p>
                            </div>
                            <label class="inline-flex items-center space-x-2">
                                <input type="checkbox" wire:model="notificationPreferences.categories.{{ $categoryKey }}.enabled" class="h-4 w-4 text-blue-600 rounded">
                                <span class="text-xs text-gray-600">{{ __('common.enabled') }}</span>
                            </label>
                        </div>
                        <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <label class="block text-xs text-gray-600">
                                {{ __('notifications.notification_category_priority') }}
                                <select wire:model="notificationPreferences.categories.{{ $categoryKey }}.priority" class="mt-1 w-full p-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    @foreach($notificationPriorities as $priority)
                                        <option value="{{ $priority }}">{{ ucfirst($priority) }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <label class="block text-xs text-gray-600">
                                {{ __('notifications.notification_category_frequency') }}
                                <select wire:model="notificationPreferences.categories.{{ $categoryKey }}.frequency" class="mt-1 w-full p-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    @foreach($notificationFrequencies as $frequencyKey => $frequencyLabel)
                                        <option value="{{ $frequencyKey }}">{{ $frequencyLabel }}</option>
                                    @endforeach
                                </select>
                            </label>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Digest scheduling --}}
            <div class="bg-white border border-gray-200 rounded-lg p-4">
                <h3 class="text-sm font-semibold text-gray-700 mb-2">{{ __('notifications.notification_digest_heading') }}</h3>
                <p class="text-xs text-gray-500 mb-4">{{ __('notifications.notification_digest_description') }}</p>
                <div class="flex items-center space-x-3 mb-4">
                    <input type="checkbox" wire:model="notificationPreferences.digest.enabled" class="h-4 w-4 text-blue-600 rounded">
                    <span class="text-sm text-gray-700">{{ __('notifications.notification_digest_enabled') }}</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <label class="block text-xs text-gray-600">
                        {{ __('notifications.notification_digest_interval') }}
                        <select wire:model="notificationPreferences.digest.interval" class="mt-1 w-full p-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @foreach(config('notifications.digest.intervals', []) as $interval)
                                <option value="{{ $interval }}">{{ ucfirst($interval) }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="block text-xs text-gray-600">
                        {{ __('notifications.notification_digest_time') }}
                        <input type="time" wire:model="notificationPreferences.digest.send_time" class="mt-1 w-full p-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </label>
                </div>
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
