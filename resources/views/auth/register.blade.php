<x-guest-layout>
    <div class="text-center mb-8">
        <h1 class="text-2xl font-bold text-indigo-700 dark:text-indigo-300">{{ __('auth.join_community') }}</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-2">{{ __('auth.register_subtitle') }}</p>
    </div>
    
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div class="mb-6">
            <x-input-label for="name" :value="__('auth.name')" class="text-gray-700 dark:text-gray-300" />
            <div class="mt-1 relative rounded-md shadow-sm">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <x-icons.user class="h-5 w-5 text-gray-400" />
                </div>
                <x-text-input id="name" 
                    class="block w-full pl-10 border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" 
                    type="text" 
                    name="name" 
                    :value="old('name')" 
                    required 
                    autofocus 
                    autocomplete="name" 
                    placeholder="{{ __('auth.name_placeholder') }}" />
            </div>
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mb-6">
            <x-input-label for="email" :value="__('auth.email')" class="text-gray-700 dark:text-gray-300" />
            <div class="mt-1 relative rounded-md shadow-sm">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <x-icons.mail class="h-5 w-5 text-gray-400" />
                </div>
                <x-text-input id="email" 
                    class="block w-full pl-10 border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" 
                    type="email" 
                    name="email" 
                    :value="old('email')" 
                    required 
                    autocomplete="username" 
                    placeholder="your-email@example.com" />
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mb-6">
            <x-input-label for="password" :value="__('auth.password')" class="text-gray-700 dark:text-gray-300" />
            <div class="mt-1 relative rounded-md shadow-sm">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <x-icons.lock class="h-5 w-5 text-gray-400" />
                </div>
                <x-text-input id="password" 
                    class="block w-full pl-10 border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" 
                    type="password" 
                    name="password" 
                    required 
                    autocomplete="new-password" 
                    placeholder="••••••••" />
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('auth.password_requirements') }}</p>
        </div>

        <!-- Confirm Password -->
        <div class="mb-6">
            <x-input-label for="password_confirmation" :value="__('auth.confirm_password')" class="text-gray-700 dark:text-gray-300" />
            <div class="mt-1 relative rounded-md shadow-sm">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <x-icons.lock class="h-5 w-5 text-gray-400" />
                </div>
                <x-text-input id="password_confirmation" 
                    class="block w-full pl-10 border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" 
                    type="password" 
                    name="password_confirmation" 
                    required 
                    autocomplete="new-password" 
                    placeholder="••••••••" />
            </div>
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="mb-6">
            <label class="flex items-start">
                <input type="checkbox" class="mt-1 rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800" name="terms" required>
                <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">
                    {!! __('auth.agree_terms_and_conditions', [
                        'terms_url' => '#',
                        'privacy_url' => '#',
                    ]) !!}
                </span>
            </label>
        </div>

        <div class="mb-6">
            <x-primary-button class="w-full justify-center py-3 text-base">
                <x-icons.user-plus class="h-5 w-5 mr-2" />
                {{ __('auth.register') }}
            </x-primary-button>
        </div>
        
        <div class="text-center text-sm text-gray-600 dark:text-gray-400">
            {{ __('auth.already_have_account') }} 
            <a href="{{ route('login') }}" class="font-medium text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300">
                {{ __('auth.login_now') }}
            </a>
        </div>
    </form>
</x-guest-layout>
