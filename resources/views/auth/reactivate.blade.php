<x-guest-layout>
    {{-- Reactivation introduction --}}
    <div class="mb-6 text-center">
        <h1 class="text-2xl font-bold text-indigo-700 dark:text-indigo-300">{{ __('auth.reactivate_account') }}</h1>
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ __('auth.reactivate_account_text') }}</p>
    </div>

    {{-- Reactivation feedback --}}
    <x-auth-session-status class="mb-4" :status="session('status')" />

    {{-- Account reactivation form --}}
    <form method="POST" action="{{ route('account.reactivate.post') }}" class="space-y-6">
        @csrf

        {{-- Email address field --}}
        <div>
            <x-input-label for="email" :value="__('auth.email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="email" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        {{-- Password field --}}
        <div>
            <x-input-label for="password" :value="__('auth.password')" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        {{-- Reactivation action --}}
        <div class="flex items-center justify-between">
            <a class="text-sm text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300" href="{{ route('login') }}">
                {{ __('auth.back_to_login') }}
            </a>
            <x-primary-button>
                {{ __('auth.reactivate_account_cta') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
