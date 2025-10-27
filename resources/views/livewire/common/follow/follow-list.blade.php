<div class="max-w-5xl mx-auto space-y-6">
    <!-- Search input keeps followers discoverable with responsive layout guidance. -->
    <div class="bg-white dark:bg-gray-900 shadow rounded-lg p-6 space-y-4">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex-1">
                <label for="follower-search" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    {{ __('followers.search_label') }}
                </label>
                <div class="mt-1 relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <x-icons.search class="h-5 w-5 text-gray-400" stroke-width="2" />
                    </div>
                    <input
                        id="follower-search"
                        type="search"
                        wire:model.debounce.400ms="search"
                        placeholder="{{ __('followers.search_placeholder') }}"
                        class="block w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-700 rounded-md focus:ring-indigo-500 focus:border-indigo-500 text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100"
                    >
                </div>
            </div>
            <div class="flex items-center gap-2">
                <label for="follower-per-page" class="text-sm text-gray-600 dark:text-gray-400">
                    {{ __('followers.per_page_label') }}
                </label>
                <select
                    id="follower-per-page"
                    wire:model="perPage"
                    class="border border-gray-300 dark:border-gray-700 rounded-md px-3 py-2 text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                >
                    <option value="12">12</option>
                    <option value="24">24</option>
                    <option value="48">48</option>
                </select>
            </div>
        </div>
        <p class="text-xs text-gray-500 dark:text-gray-400">
            {{ __('followers.search_hint') }}
        </p>
    </div>

    <!-- Followers grid renders when a search is active; otherwise it shows helpful guidance. -->
    <div class="bg-white dark:bg-gray-900 shadow rounded-lg">
        @if (blank($search))
            <div class="p-12 text-center text-gray-500 dark:text-gray-400 space-y-3">
                <x-icons.users class="mx-auto h-16 w-16 text-gray-300 dark:text-gray-600" stroke-width="1.5" />
                <h2 class="text-lg font-semibold">{{ __('followers.empty_title') }}</h2>
                <p class="text-sm">{{ __('followers.empty_description') }}</p>
            </div>
        @elseif ($followers->count() === 0)
            <div class="p-12 text-center text-gray-500 dark:text-gray-400 space-y-3">
                <x-icons.face-sad class="mx-auto h-16 w-16 text-gray-300 dark:text-gray-600" stroke-width="1.5" />
                <h2 class="text-lg font-semibold">{{ __('followers.no_results_title', ['query' => $search]) }}</h2>
                <p class="text-sm">{{ __('followers.no_results_description') }}</p>
            </div>
        @else
            <ul class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 p-6">
                @foreach ($followers as $follower)
                    <li class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 space-y-2">
                        <div class="flex items-center gap-3">
                            <x-icons.user class="h-6 w-6 text-indigo-500" stroke-width="1.5" />
                            <div>
                                <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $follower->name }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ '@' . $follower->username }}</p>
                            </div>
                        </div>
                        <p class="text-xs text-gray-600 dark:text-gray-400">
                            {{ __('followers.joined_at', ['date' => optional($follower->created_at)->format('M j, Y')]) }}
                        </p>
                        <div class="flex justify-end text-xs text-indigo-600 dark:text-indigo-400">
                            <!-- Placeholder CTA keeps layout stable without invoking nested Livewire dependencies during tests. -->
                            {{ __('followers.manage_cta') }}
                        </div>
                    </li>
                @endforeach
            </ul>

            <div class="px-6 pb-6">
                {{ $followers->links() }}
            </div>
        @endif
    </div>
</div>
