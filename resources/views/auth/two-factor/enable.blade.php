@extends('layouts.app')

@section('content')
    {{-- Container that presents the full two-factor security setup details --}}
    <div class="bg-white dark:bg-gray-900 shadow rounded-xl p-8">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">{{ __('auth.two_factor_heading') }}</h1>
        <p class="text-gray-600 dark:text-gray-300 mb-6">{{ __('auth.two_factor_description') }}</p>

        @if (session('status'))
            <div class="mb-6 p-4 rounded-lg border border-green-200 bg-green-50 text-green-800 dark:border-green-700 dark:bg-green-900/40 dark:text-green-200">
                {{-- Feedback message after enabling, disabling, or removing a device --}}
                {{ session('status') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="space-y-4">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">{{ __('auth.two_factor_setup_steps') }}</h2>
                <ol class="list-decimal list-inside text-gray-700 dark:text-gray-300 space-y-2">
                    <li>{{ __('auth.two_factor_step_one') }}</li>
                    <li>{{ __('auth.two_factor_step_two') }}</li>
                    <li>{{ __('auth.two_factor_step_three') }}</li>
                </ol>

                <div class="mt-6">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200">{{ __('auth.two_factor_use_secret') }}</h3>
                    <div class="mt-2 p-4 bg-gray-100 dark:bg-gray-800 rounded-lg font-mono text-lg text-gray-900 dark:text-gray-100">
                        {{-- Display the shared secret so that the authenticator app can be configured manually --}}
                        {{ $secretKey }}
                    </div>
                </div>

                <div class="mt-6">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200">{{ __('auth.two_factor_recovery_codes') }}</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">{{ __('auth.two_factor_recovery_codes_help') }}</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        @foreach ($recoveryCodes as $code)
                            <span class="p-3 bg-indigo-50 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-200 rounded-lg font-semibold tracking-widest">{{ $code }}</span>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-6 text-center">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">{{ __('auth.two_factor_scan_qr') }}</h3>
                    <div class="inline-block p-4 bg-white dark:bg-gray-800 rounded-xl shadow">
                        {{-- Inline SVG that contains the QR code for authenticator apps --}}
                        {!! $qrCodeSvg !!}
                    </div>
                </div>

                @if (!auth()->user()->two_factor_enabled)
                    <form method="POST" action="{{ route('two-factor.confirm') }}" class="space-y-4">
                        @csrf
                        <label class="block text-left">
                            <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">{{ __('auth.two_factor_enter_code') }}</span>
                            <input type="text" name="code" required maxlength="6" minlength="6" pattern="[0-9]{6}" autocomplete="one-time-code" class="mt-2 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500" placeholder="123456">
                        </label>
                        @error('code')
                            <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror

                        <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg shadow focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{-- Confirm button enabling two-factor authentication --}}
                            {{ __('auth.two_factor_verify_button') }}
                        </button>
                    </form>
                @else
                    <div class="p-4 bg-indigo-50 dark:bg-indigo-900/40 border border-indigo-200 dark:border-indigo-700 rounded-lg text-left">
                        {{-- Guidance shown when two-factor authentication is already active --}}
                        <p class="text-sm text-indigo-800 dark:text-indigo-200">{{ __('auth.two_factor_status_enabled') }}</p>
                    </div>
                @endif

                <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-3">{{ __('auth.two_factor_manage_devices') }}</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">{{ __('auth.two_factor_manage_devices_help') }}</p>
                    <div class="space-y-3">
                        @forelse ($devices as $device)
                            <div class="flex flex-col md:flex-row md:items-center md:justify-between p-4 bg-gray-50 dark:bg-gray-800/60 border border-gray-200 dark:border-gray-700 rounded-lg">
                                <div class="space-y-1">
                                    <p class="text-sm font-semibold text-gray-800 dark:text-gray-100">{{ $device->name }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('auth.two_factor_device_last_used', ['date' => optional($device->last_used_at)->diffForHumans() ?? __('auth.two_factor_device_never')]) }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $device->ip_address }}</p>
                                </div>
                                <form method="POST" action="{{ route('two-factor.devices.destroy', $device) }}" class="mt-3 md:mt-0">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center px-3 py-2 text-sm font-semibold text-red-600 hover:text-red-700 focus:outline-none focus:underline">
                                        {{-- Allow the user to revoke a trusted device --}}
                                        {{ __('auth.two_factor_forget_device') }}
                                    </button>
                                </form>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('auth.two_factor_no_devices') }}</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
