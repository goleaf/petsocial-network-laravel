<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('common.dashboard') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Activity Feed -->
                <div class="md:col-span-2 space-y-6">
                    <!-- Create Post -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">{{ __('common.create_post') }}</h3>
                            <div class="flex items-start space-x-4">
                                <div class="flex-shrink-0">
                                    <img class="h-10 w-10 rounded-full" src="{{ auth()->user()->profile_photo_url }}" alt="{{ auth()->user()->name }}">
                                </div>
                                <div class="min-w-0 flex-1">
                                    <form action="#" method="POST" class="relative">
                                        @csrf
                                        <div class="border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm overflow-hidden">
                                            <textarea rows="3" name="content" id="content" class="block w-full py-3 px-4 border-0 resize-none focus:ring-0 sm:text-sm dark:bg-gray-700 dark:text-gray-300" placeholder="{{ __('common.whats_on_your_mind') }}"></textarea>
                                        </div>
                                        <div class="flex items-center justify-between pt-3">
                                            <div class="flex items-center space-x-5">
                                                <button type="button" class="-m-2 p-2 rounded-full flex items-center text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200">
                                                    <x-icons.photo class="h-5 w-5" />
                                                    <span class="text-sm font-medium ml-1">{{ __('common.photo') }}</span>
                                                </button>
                                                <button type="button" class="-m-2 p-2 rounded-full flex items-center text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200">
                                                    <x-icons.tag class="h-5 w-5" />
                                                    <span class="text-sm font-medium ml-1">{{ __('common.tag') }}</span>
                                                </button>
                                            </div>
                                            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                {{ __('common.post') }}
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">{{ __('common.recent_activity') }}</h3>
                            <div class="space-y-6">
                                @for ($i = 0; $i < 3; $i++)
                                <div class="flex space-x-3">
                                    <div class="flex-shrink-0">
                                        <img class="h-10 w-10 rounded-full" src="https://images.unsplash.com/photo-{{ 1500000000 + $i * 100 }}?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80" alt="">
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                            <a href="#" class="hover:underline">{{ ['Max', 'Bella', 'Charlie'][$i] }}</a>
                                        </p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            <a href="#" class="hover:underline">
                                                {{ [__('common.added_new_photo'), __('common.made_new_friend'), __('common.updated_profile')][$i] }}
                                            </a>
                                        </p>
                                        <div class="mt-2 text-sm text-gray-700 dark:text-gray-300">
                                            <p>{{ [__('common.sample_post_1'), __('common.sample_post_2'), __('common.sample_post_3')][$i] }}</p>
                                        </div>
                                        <div class="mt-2 flex items-center space-x-4">
                                            <button type="button" class="flex items-center text-sm text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200">
                                                <x-icons.heart class="h-4 w-4 mr-1" />
                                                {{ rand(5, 20) }}
                                            </button>
                                            <button type="button" class="flex items-center text-sm text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200">
                                                <x-icons.chat class="h-4 w-4 mr-1" />
                                                {{ rand(1, 10) }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                @endfor
                            </div>
                            <div class="mt-6">
                                <a href="{{ route('activity') }}" class="w-full flex justify-center items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600">
                                    {{ __('common.view_all_activity') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Sidebar -->
                <div class="space-y-6">
                    <!-- Your Pets -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ __('common.your_pets') }}</h3>
                                <a href="{{ route('pets') }}" class="text-sm text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300">{{ __('common.view_all') }}</a>
                            </div>
                            <div class="space-y-4">
                                @for ($i = 0; $i < 3; $i++)
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0">
                                        <img class="h-10 w-10 rounded-full" src="https://images.unsplash.com/photo-{{ 1600000000 + $i * 100 }}?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80" alt="">
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                            <a href="#" class="hover:underline">{{ ['Buddy', 'Luna', 'Rocky'][$i] }}</a>
                                        </p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ ['Golden Retriever', 'Siamese Cat', 'German Shepherd'][$i] }}
                                        </p>
                                    </div>
                                </div>
                                @endfor
                            </div>
                            <div class="mt-6">
                                <a href="{{ route('pets') }}" class="w-full flex justify-center items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600">
                                    {{ __('common.add_pet') }}
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Friend Requests -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ __('friendships.friend_requests') }}</h3>
                                <a href="{{ route('friendships.index') }}" class="text-sm text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300">{{ __('common.view_all') }}</a>
                            </div>
                            <div class="space-y-4">
                                @for ($i = 0; $i < 2; $i++)
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-3">
                                        <div class="flex-shrink-0">
                                            <img class="h-10 w-10 rounded-full" src="https://images.unsplash.com/photo-{{ 1700000000 + $i * 100 }}?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80" alt="">
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                <a href="#" class="hover:underline">{{ ['Sophie', 'Oliver'][$i] }}</a>
                                            </p>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ ['3 mutual friends', '1 mutual friend'][$i] }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex space-x-2">
                                        <button type="button" class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                            {{ __('friendships.accept') }}
                                        </button>
                                        <button type="button" class="inline-flex items-center px-2.5 py-1.5 border border-gray-300 text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600">
                                            {{ __('friendships.decline') }}
                                        </button>
                                    </div>
                                </div>
                                @endfor
                            </div>
                            <div class="mt-6">
                                <a href="{{ route('friendships.index') }}" class="w-full flex justify-center items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600">
                                    {{ __('friendships.manage_requests') }}
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Trending Tags -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">{{ __('common.trending_tags') }}</h3>
                            <div class="flex flex-wrap gap-2">
                                @foreach(['#doglife', '#catsofinstagram', '#petlover', '#cuteanimals', '#adoptdontshop'] as $tag)
                                <a href="#" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-indigo-100 text-indigo-800 hover:bg-indigo-200 dark:bg-indigo-800 dark:text-indigo-100 dark:hover:bg-indigo-700">
                                    {{ $tag }}
                                </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
