<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('common.profile') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Profile Sidebar -->
                <div class="md:col-span-1 space-y-6">
                    <!-- Profile Card -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex flex-col items-center text-center">
                                <div class="relative">
                                    <img class="h-32 w-32 rounded-full object-cover" src="{{ auth()->user()->profile_photo_url }}" alt="{{ auth()->user()->name }}">
                                    <button type="button" class="absolute bottom-0 right-0 bg-indigo-600 p-1 rounded-full text-white hover:bg-indigo-700">
                                        <x-icons.edit class="h-4 w-4" />
                                    </button>
                                </div>
                                <h3 class="mt-4 text-xl font-medium text-gray-900 dark:text-gray-100">{{ auth()->user()->name }}</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ auth()->user()->email }}</p>
                                <div class="mt-3 flex justify-center space-x-4">
                                    <div class="text-center">
                                        <span class="block font-bold text-gray-900 dark:text-gray-100">{{ rand(10, 50) }}</span>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ __('common.friends') }}</span>
                                    </div>
                                    <div class="text-center">
                                        <span class="block font-bold text-gray-900 dark:text-gray-100">{{ rand(1, 5) }}</span>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ __('common.pets') }}</span>
                                    </div>
                                    <div class="text-center">
                                        <span class="block font-bold text-gray-900 dark:text-gray-100">{{ rand(5, 30) }}</span>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ __('common.posts') }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-6 border-t border-gray-200 dark:border-gray-700 pt-4">
                                <div class="flex flex-col space-y-3">
                                    <a href="{{ route('friends') }}" class="flex items-center text-gray-700 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400">
                                        <x-icons.friends class="h-5 w-5 mr-2" />
                                        {{ __('common.my_friends') }}
                                    </a>
                                    <a href="{{ route('pets') }}" class="flex items-center text-gray-700 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400">
                                        <x-icons.pets class="h-5 w-5 mr-2" />
                                        {{ __('common.my_pets') }}
                                    </a>
                                    <a href="#" class="flex items-center text-gray-700 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400">
                                        <x-icons.photos class="h-5 w-5 mr-2" />
                                        {{ __('common.my_photos') }}
                                    </a>
                                    <a href="#" class="flex items-center text-gray-700 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400">
                                        <x-icons.activity class="h-5 w-5 mr-2" />
                                        {{ __('common.my_activity') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Profile Content -->
                <div class="md:col-span-2 space-y-6">
                    <!-- Profile Information -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow sm:rounded-lg">
                        <div class="p-6">
                            @include('profile.partials.update-profile-information-form')
                        </div>
                    </div>

                    <!-- Password Update -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow sm:rounded-lg">
                        <div class="p-6">
                            @include('profile.partials.update-password-form')
                        </div>
                    </div>

                    <!-- Account Management -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow sm:rounded-lg">
                        <div class="p-6">
                            @include('profile.partials.delete-user-form')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
