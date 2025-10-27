<x-guest-layout>
    {{-- Card prompting for a secondary verification factor --}}
    <div class="text-center mb-8">
        <h1 class="text-2xl font-bold text-indigo-700 dark:text-indigo-300">{{ __('auth.two_factor_challenge_title') }}</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-2">{{ __('auth.two_factor_challenge_description') }}</p>
    </div>

    @if ($errors->any())
        <div class="mb-6 p-4 rounded-lg border border-red-200 bg-red-50 text-red-700 dark:border-red-700 dark:bg-red-900/40 dark:text-red-200">
            {{-- Surface all validation feedback for the verification attempt --}}
            <ul class="list-disc list-inside text-left">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('two-factor.verify') }}" class="space-y-6">
        @csrf
        <div>
            <label for="code" class="block text-sm font-semibold text-gray-700 dark:text-gray-300">{{ __('auth.two_factor_enter_code') }}</label>
            <input id="code" type="text" name="code" required autofocus maxlength="10" class="mt-2 block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500" placeholder="123456 or recovery code">
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">{{ __('auth.two_factor_use_recovery') }}</p>
        </div>

        <div class="flex items-center">
            <input id="remember_device" name="remember_device" type="checkbox" value="1" class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800">
            <label for="remember_device" class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ __('auth.two_factor_remember_device') }}</label>
        </div>

        <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg shadow focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            {{-- Submit button verifying the one-time password --}}
            {{ __('auth.two_factor_verify_button') }}
        </button>
    </form>
</x-guest-layout>
