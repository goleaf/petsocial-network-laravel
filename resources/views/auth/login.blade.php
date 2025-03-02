<x-guest-layout>
    <div class="text-center mb-8">
        <h1 class="text-2xl font-bold text-indigo-700 dark:text-indigo-300">{{ __('auth.welcome_back') }}</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-2">{{ __('auth.login_subtitle') }}</p>
    </div>
    
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

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
                    autofocus 
                    autocomplete="username" 
                    placeholder="your-email@example.com" />
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <x-input-label for="password" :value="__('auth.password')" class="text-gray-700 dark:text-gray-300" />
                @if (Route::has('password.request'))
                    <a class="text-sm text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300" href="{{ route('password.request') }}">
                        {{ __('auth.forgot_password') }}
                    </a>
                @endif
            </div>
            <div class="mt-1 relative rounded-md shadow-sm">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <x-icons.lock class="h-5 w-5 text-gray-400" />
                </div>
                <x-text-input id="password" 
                    class="block w-full pl-10 border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" 
                    type="password" 
                    name="password" 
                    required 
                    autocomplete="current-password" 
                    placeholder="••••••••" />
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="flex items-center justify-between mb-6">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800" name="remember">
                <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ __('auth.remember_me') }}</span>
            </label>
        </div>

        <div class="mb-6">
            <x-primary-button class="w-full justify-center py-3 text-base">
                <x-icons.login class="h-5 w-5 mr-2" />
                {{ __('auth.login') }}
            </x-primary-button>
        </div>
        
        <div class="text-center text-sm text-gray-600 dark:text-gray-400">
            {{ __('auth.no_account') }} 
            <a href="{{ route('register') }}" class="font-medium text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300">
                {{ __('auth.register_now') }}
            </a>
        </div>
    </form>
</x-guest-layout>
