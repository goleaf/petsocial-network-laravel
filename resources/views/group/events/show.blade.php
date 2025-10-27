<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                {{-- Present the event name alongside the group for quick orientation. --}}
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    {{ $event->title }}
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ __('Group') }}: {{ $group->name }}
                </p>
            </div>
            <a
                href="{{ route('group.event.export', ['group' => $group, 'event' => $event]) }}"
                class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
            >
                {{-- Provide a clear call-to-action for calendar exports. --}}
                {{ __('Download iCal') }}
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 space-y-6">
                    <section>
                        {{-- Highlight the core schedule so attendees know when to join. --}}
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                            {{ __('Schedule') }}
                        </h3>
                        <p class="text-sm text-gray-600 dark:text-gray-300">
                            {{ $event->start_date->toDayDateTimeString() }}
                            @if ($event->end_date)
                                &ndash; {{ $event->end_date->toDayDateTimeString() }}
                            @endif
                        </p>
                    </section>

                    <section>
                        {{-- Surface location details with a friendly fallback for online meetups. --}}
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                            {{ __('Location') }}
                        </h3>
                        @if ($event->is_online)
                            <p class="text-sm text-gray-600 dark:text-gray-300">
                                {{ $event->online_meeting_url ?? __('Online Event') }}
                            </p>
                        @elseif ($event->location)
                            <p class="text-sm text-gray-600 dark:text-gray-300">
                                {{ $event->location }}
                            </p>
                        @else
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                {{ __('Location details will be shared soon.') }}
                            </p>
                        @endif
                    </section>

                    <section>
                        {{-- Share the description so members understand what to expect. --}}
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                            {{ __('About this event') }}
                        </h3>
                        <p class="text-sm text-gray-600 dark:text-gray-300 whitespace-pre-line">
                            {{ $event->description ?: __('No description provided yet.') }}
                        </p>
                    </section>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
