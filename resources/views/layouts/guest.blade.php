<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Pets Social Network') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gradient-to-b from-indigo-100 to-purple-100 dark:from-gray-900 dark:to-indigo-950">
            <div class="absolute top-4 right-4">
                <x-language-switcher />
            </div>
            
            <div class="w-full sm:max-w-4xl px-6 py-8">
                <div class="flex flex-col md:flex-row items-center justify-between">
                    <div class="flex items-center mb-6 md:mb-0">
                        <a href="/" class="flex items-center">
                            <x-application-logo class="w-16 h-16 fill-current text-indigo-600" />
                            <span class="ml-3 text-3xl font-bold text-indigo-700 dark:text-indigo-300">{{ __('common.pets_social_network') }}</span>
                        </a>
                    </div>
                    <div class="flex space-x-4">
                        <a href="{{ route('login') }}" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 font-medium">{{ __('auth.login') }}</a>
                        <a href="{{ route('register') }}" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 font-medium">{{ __('auth.register') }}</a>
                    </div>
                </div>
            </div>

            <div class="w-full flex flex-col md:flex-row max-w-6xl px-6 py-4">
                <!-- Left side - Pet illustrations or features -->
                <div class="w-full md:w-1/2 p-6 flex items-center justify-center">
                    <div class="max-w-md">
                        <h1 class="text-3xl md:text-4xl font-bold text-indigo-800 dark:text-indigo-300 mb-4">{{ __('common.welcome_message') }}</h1>
                        <p class="text-lg text-gray-700 dark:text-gray-300 mb-6">{{ __('common.welcome_description') }}</p>
                        <div class="flex flex-wrap gap-4 mb-8">
                            <div class="flex items-center">
                                <x-icons.paw class="h-6 w-6 text-indigo-600 mr-2" />
                                <span class="text-gray-700 dark:text-gray-300">{{ __('common.feature_pets') }}</span>
                            </div>
                            <div class="flex items-center">
                                <x-icons.users class="h-6 w-6 text-indigo-600 mr-2" />
                                <span class="text-gray-700 dark:text-gray-300">{{ __('common.feature_community') }}</span>
                            </div>
                            <div class="flex items-center">
                                <x-icons.photo class="h-6 w-6 text-indigo-600 mr-2" />
                                <span class="text-gray-700 dark:text-gray-300">{{ __('common.feature_photos') }}</span>
                            </div>
                            <div class="flex items-center">
                                <x-icons.calendar class="h-6 w-6 text-indigo-600 mr-2" />
                                <span class="text-gray-700 dark:text-gray-300">{{ __('common.feature_events') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Right side - Auth form -->
                <div class="w-full md:w-1/2 p-6">
                    <div class="bg-white dark:bg-gray-800 shadow-xl rounded-xl p-8 border border-gray-200 dark:border-gray-700">
                        {{ $slot }}
                    </div>
                </div>
            </div>
            
            <div class="w-full max-w-6xl px-6 py-4 mt-8">
                <div class="border-t border-gray-300 dark:border-gray-700 pt-6 text-center text-gray-600 dark:text-gray-400">
                    <p>© {{ date('Y') }} {{ __('common.pets_social_network') }}. {{ __('common.all_rights_reserved') }}</p>
                </div>
            </div>
        </div>
    </body>
</html>
