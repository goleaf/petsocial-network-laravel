<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
        {{ __('auth.forgot_password_text') }}
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('auth.email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

    <div class="flex items-center justify-between mt-4">
        {{-- Reactivation helper link --}}
        <a class="text-sm text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300" href="{{ route('account.reactivate') }}">
            {{ __('auth.account_reactivate_link') }}
        </a>
        <x-primary-button>
            {{ __('auth.send_reset_link') }}
        </x-primary-button>
    </div>
    </form>
</x-guest-layout>
