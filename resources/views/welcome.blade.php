<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('welcome.title') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-gradient-to-b from-indigo-50 to-purple-50 dark:from-gray-900 dark:to-indigo-950">
    <div class="relative min-h-screen">
        <!-- Header -->
        <header class="bg-white dark:bg-gray-800 shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 flex items-center">
                            <x-application-logo class="block h-10 w-auto fill-current text-indigo-600 dark:text-indigo-400" />
                            <span class="ml-3 text-xl font-bold text-indigo-700 dark:text-indigo-300">{{ __('common.pets_social_network') }}</span>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        <x-language-switcher />
                        @if (Route::has('login'))
                            <div class="flex space-x-4">
                                @auth
                                    <a href="{{ route('dashboard') }}" class="font-semibold text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300">{{ __('common.dashboard') }}</a>
                                @else
                                    <a href="{{ route('login') }}" class="font-semibold text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300">{{ __('auth.login') }}</a>
                                    @if (Route::has('register'))
                                        <a href="{{ route('register') }}" class="font-semibold text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300">{{ __('auth.register') }}</a>
                                    @endif
                                @endauth
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </header>

        <!-- Hero Section -->
        <div class="py-12 bg-gradient-to-r from-indigo-500 to-purple-600 dark:from-indigo-800 dark:to-purple-900">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col md:flex-row items-center">
                    <div class="md:w-1/2 mb-8 md:mb-0">
                        <h1 class="text-4xl sm:text-5xl font-bold text-white mb-4">{{ __('welcome.welcome_message') }}</h1>
                        <p class="text-xl text-indigo-100 mb-8">{{ __('welcome.welcome_description') }}</p>
                        <div class="flex space-x-4">
                            <a href="{{ route('register') }}" class="px-6 py-3 bg-white text-indigo-600 font-semibold rounded-lg shadow-md hover:bg-indigo-50 transition duration-300">
                                {{ __('auth.register') }}
                            </a>
                            <a href="{{ route('login') }}" class="px-6 py-3 bg-transparent border-2 border-white text-white font-semibold rounded-lg hover:bg-white hover:text-indigo-600 transition duration-300">
                                {{ __('auth.login') }}
                            </a>
                        </div>
                    </div>
                    <div class="md:w-1/2 flex justify-center">
                        <img src="https://images.unsplash.com/photo-1560807707-8cc77767d783?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1035&q=80" alt="Pets" class="rounded-xl shadow-2xl max-w-md w-full h-auto object-cover">
                    </div>
                </div>
            </div>
        </div>

        <!-- Features Section -->
        <div class="py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <h2 class="text-3xl font-bold text-gray-900 dark:text-white">{{ __('welcome.why_join_us') }}</h2>
                    <p class="mt-4 text-xl text-gray-600 dark:text-gray-300">{{ __('welcome.join_benefits') }}</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <!-- Feature 1 -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-300">
                        <div class="p-8">
                            <div class="flex items-center justify-center h-12 w-12 rounded-md bg-indigo-500 text-white mb-4">
                                <x-icons.paw class="h-6 w-6" />
                            </div>
                            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">{{ __('welcome.feature_pets') }}</h3>
                            <p class="text-gray-600 dark:text-gray-300">{{ __('welcome.feature_pets_description') }}</p>
                        </div>
                    </div>
                    <!-- Feature 2 -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-300">
                        <div class="p-8">
                            <div class="flex items-center justify-center h-12 w-12 rounded-md bg-indigo-500 text-white mb-4">
                                <x-icons.users class="h-6 w-6" />
                            </div>
                            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">{{ __('welcome.feature_community') }}</h3>
                            <p class="text-gray-600 dark:text-gray-300">{{ __('welcome.feature_community_description') }}</p>
                        </div>
                    </div>
                    <!-- Feature 3 -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-300">
                        <div class="p-8">
                            <div class="flex items-center justify-center h-12 w-12 rounded-md bg-indigo-500 text-white mb-4">
                                <x-icons.calendar class="h-6 w-6" />
                            </div>
                            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">{{ __('welcome.feature_events') }}</h3>
                            <p class="text-gray-600 dark:text-gray-300">{{ __('welcome.feature_events_description') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Testimonials Section -->
        <div class="py-12 bg-indigo-50 dark:bg-gray-900">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <h2 class="text-3xl font-bold text-gray-900 dark:text-white">{{ __('welcome.testimonials') }}</h2>
                    <p class="mt-4 text-xl text-gray-600 dark:text-gray-300">{{ __('welcome.testimonials_description') }}</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <!-- Testimonial 1 -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden p-6">
                        <div class="flex items-center mb-4">
                            <img class="h-12 w-12 rounded-full object-cover" src="https://images.unsplash.com/photo-1517849845537-4d257902454a?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=300&q=80" alt="User">
                            <div class="ml-4">
                                <h4 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('welcome.testimonial_name_1') }}</h4>
                                <p class="text-gray-600 dark:text-gray-400">{{ __('welcome.testimonial_pet_1') }}</p>
                            </div>
                        </div>
                        <p class="text-gray-600 dark:text-gray-300">{{ __('welcome.testimonial_text_1') }}</p>
                    </div>
                    <!-- Testimonial 2 -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden p-6">
                        <div class="flex items-center mb-4">
                            <img class="h-12 w-12 rounded-full object-cover" src="https://images.unsplash.com/photo-1543466835-00a7907e9de1?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=300&q=80" alt="User">
                            <div class="ml-4">
                                <h4 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('welcome.testimonial_name_2') }}</h4>
                                <p class="text-gray-600 dark:text-gray-400">{{ __('welcome.testimonial_pet_2') }}</p>
                            </div>
                        </div>
                        <p class="text-gray-600 dark:text-gray-300">{{ __('welcome.testimonial_text_2') }}</p>
                    </div>
                    <!-- Testimonial 3 -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden p-6">
                        <div class="flex items-center mb-4">
                            <img class="h-12 w-12 rounded-full object-cover" src="https://images.unsplash.com/photo-1573865526739-10659fec78a5?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=300&q=80" alt="User">
                            <div class="ml-4">
                                <h4 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('welcome.testimonial_name_3') }}</h4>
                                <p class="text-gray-600 dark:text-gray-400">{{ __('welcome.testimonial_pet_3') }}</p>
                            </div>
                        </div>
                        <p class="text-gray-600 dark:text-gray-300">{{ __('welcome.testimonial_text_3') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- CTA Section -->
        <div class="py-12 bg-indigo-600 dark:bg-indigo-800">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h2 class="text-3xl font-bold text-white mb-4">{{ __('welcome.ready_to_join') }}</h2>
                <p class="text-xl text-indigo-100 mb-8">{{ __('welcome.join_today_message') }}</p>
                <a href="{{ route('register') }}" class="inline-block px-8 py-4 bg-white text-indigo-600 font-bold rounded-lg shadow-md hover:bg-indigo-50 transition duration-300 text-lg">
                    {{ __('welcome.get_started') }}
                </a>
            </div>
        </div>

        <!-- Footer -->
        <footer class="bg-white dark:bg-gray-800 py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col md:flex-row justify-between items-center">
                    <div class="flex items-center mb-4 md:mb-0">
                        <x-application-logo class="block h-8 w-auto fill-current text-indigo-600 dark:text-indigo-400" />
                        <span class="ml-2 text-lg font-semibold text-gray-900 dark:text-white">{{ __('common.pets_social_network') }}</span>
                    </div>
                    <div class="flex space-x-6">
                        <a href="#" class="text-gray-600 hover:text-indigo-600 dark:text-gray-300 dark:hover:text-indigo-400">
                            <x-icons.facebook class="h-6 w-6" />
                        </a>
                        <a href="#" class="text-gray-600 hover:text-indigo-600 dark:text-gray-300 dark:hover:text-indigo-400">
                            <x-icons.twitter class="h-6 w-6" />
                        </a>
                        <a href="#" class="text-gray-600 hover:text-indigo-600 dark:text-gray-300 dark:hover:text-indigo-400">
                            <x-icons.instagram class="h-6 w-6" />
                        </a>
                    </div>
                </div>
                <div class="mt-8 border-t border-gray-200 dark:border-gray-700 pt-8 flex flex-col md:flex-row justify-between">
                    <p class="text-gray-600 dark:text-gray-300 mb-4 md:mb-0"> {{ date('Y') }} {{ __('common.pets_social_network') }}. {{ __('welcome.all_rights_reserved') }}</p>
                    <div class="flex flex-wrap space-x-6">
                        <a href="#" class="text-gray-600 hover:text-indigo-600 dark:text-gray-300 dark:hover:text-indigo-400">{{ __('welcome.privacy_policy') }}</a>
                        <a href="#" class="text-gray-600 hover:text-indigo-600 dark:text-gray-300 dark:hover:text-indigo-400">{{ __('welcome.terms_of_service') }}</a>
                        <a href="#" class="text-gray-600 hover:text-indigo-600 dark:text-gray-300 dark:hover:text-indigo-400">{{ __('welcome.contact_us') }}</a>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</body>
</html>
