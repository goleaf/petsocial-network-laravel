{{-- Group management dashboard summarising community activity trends. --}}
<div data-testid="group-management-index-root" class="space-y-10">
    {{-- Header area reinforces the page purpose and anchors the control set. --}}
    <section class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-50">{{ __('Groups & Communities') }}</h1>
            <p class="mt-2 max-w-2xl text-sm text-slate-600 dark:text-slate-300">
                {{ __('Monitor engagement, approvals, and participation rates across every community you oversee.') }}
            </p>
        </div>

        {{-- Search and filter controls let operators focus on the communities that matter most. --}}
        <div class="flex w-full flex-col gap-4 sm:flex-row sm:items-center sm:justify-end">
            <label for="group-search" class="sr-only">{{ __('Search groups') }}</label>
            <div class="relative w-full sm:w-72">
                <input
                    wire:model.debounce.500ms="search"
                    type="search"
                    id="group-search"
                    placeholder="{{ __('Search groups…') }}"
                    class="w-full rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm text-slate-900 shadow-sm focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
                >
                <div wire:loading.flex wire:target="search" class="absolute inset-y-0 right-3 hidden items-center">
                    <span class="h-3 w-3 animate-spin rounded-full border-2 border-primary-500 border-t-transparent"></span>
                </div>
            </div>

            {{-- Visibility filter toggles reuse the stored filter state. --}}
            <div class="flex flex-wrap items-center gap-2">
                @php
                    $filters = [
                        'all' => __('All'),
                        'my' => __('My groups'),
                        'open' => __('Open'),
                        'closed' => __('Closed'),
                        'secret' => __('Secret'),
                    ];
                @endphp

                @foreach ($filters as $value => $label)
                    <button
                        type="button"
                        wire:click="$set('filter', '{{ $value }}')"
                        class="rounded-full border px-3 py-1 text-sm transition focus:outline-none focus:ring-2 focus:ring-primary-500 {{ $filter === $value ? 'border-primary-500 bg-primary-50 text-primary-700 dark:border-primary-400 dark:bg-primary-900/40 dark:text-primary-100' : 'border-slate-200 text-slate-600 hover:border-primary-300 hover:text-primary-600 dark:border-slate-700 dark:text-slate-300 dark:hover:border-primary-500 dark:hover:text-primary-200' }}"
                    >
                        {{ $label }}
                    </button>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Summary metrics present a quick overview of overall community health. --}}
    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @php
            $metricCards = [
                ['label' => __('Total groups'), 'value' => number_format($summaryMetrics['total_groups'] ?? 0)],
                ['label' => __('Active members'), 'value' => number_format($summaryMetrics['active_members'] ?? 0)],
                ['label' => __('Pending approvals'), 'value' => number_format($summaryMetrics['pending_members'] ?? 0)],
                ['label' => __('Upcoming events'), 'value' => number_format($summaryMetrics['upcoming_events'] ?? 0)],
            ];
        @endphp

        @foreach ($metricCards as $card)
            <article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                <p class="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ $card['label'] }}</p>
                <p class="mt-3 text-2xl font-semibold text-slate-900 dark:text-slate-50">{{ $card['value'] }}</p>
            </article>
        @endforeach

        <article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:col-span-2 xl:col-span-4 dark:border-slate-700 dark:bg-slate-900">
            <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Engagement rate (7d)') }}</p>
                    <p class="mt-3 text-3xl font-semibold text-slate-900 dark:text-slate-50">
                        {{ number_format($summaryMetrics['engagement_rate'] ?? 0, 2) }}
                    </p>
                </div>
                <p class="text-sm text-slate-600 dark:text-slate-300">
                    {{ __('Calculated by dividing recent topics and replies by the number of active members in the filtered results.') }}
                </p>
            </div>
        </article>
    </section>

    {{-- Highlight active categories so moderators can identify thriving focus areas quickly. --}}
    @if ($categories->isNotEmpty())
        <section class="rounded-xl border border-dashed border-slate-300 bg-slate-50/60 p-4 dark:border-slate-600 dark:bg-slate-900/40">
            <p class="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Featured categories') }}</p>
            <div class="mt-3 flex flex-wrap gap-2">
                @foreach ($categories as $category)
                    <span class="rounded-full bg-white px-3 py-1 text-sm text-slate-600 shadow-sm dark:bg-slate-800 dark:text-slate-200">
                        {{ $category->name }}
                    </span>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Tabular layout surfaces per-group activity and growth signals. --}}
    <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900">
        <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
            <thead class="bg-slate-50 dark:bg-slate-800/60">
                <tr>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-300">{{ __('Group') }}</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-300">{{ __('Active members') }}</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-300">{{ __('New members (7d)') }}</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-300">{{ __('Topics (7d)') }}</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-300">{{ __('Replies (7d)') }}</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-300">{{ __('Upcoming events') }}</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-300">{{ __('Engagement rate') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse ($groups as $group)
                    @php
                        $metrics = $groupActivity[$group->id] ?? [
                            'active_members' => 0,
                            'new_members' => 0,
                            'topics_last_seven_days' => 0,
                            'replies_last_seven_days' => 0,
                            'upcoming_events' => 0,
                            'engagement_rate' => 0,
                        ];
                    @endphp

                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/60">
                        <td class="px-4 py-4 text-sm text-slate-700 dark:text-slate-200">
                            <div class="flex flex-col">
                                <span class="font-semibold text-slate-900 dark:text-slate-50">{{ $group->name }}</span>
                                <span class="text-xs text-slate-500 dark:text-slate-400">{{ $group->category->name ?? __('Uncategorised') }}</span>
                                <span class="mt-1 inline-flex w-fit rounded-full bg-slate-100 px-2 py-0.5 text-xs text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                                    {{ ucfirst($group->visibility) }}
                                </span>
                            </div>
                        </td>
                        <td class="px-4 py-4 text-sm font-semibold text-slate-900 dark:text-slate-100">{{ number_format($metrics['active_members']) }}</td>
                        <td class="px-4 py-4 text-sm text-slate-700 dark:text-slate-200">{{ number_format($metrics['new_members']) }}</td>
                        <td class="px-4 py-4 text-sm text-slate-700 dark:text-slate-200">{{ number_format($metrics['topics_last_seven_days']) }}</td>
                        <td class="px-4 py-4 text-sm text-slate-700 dark:text-slate-200">{{ number_format($metrics['replies_last_seven_days']) }}</td>
                        <td class="px-4 py-4 text-sm text-slate-700 dark:text-slate-200">{{ number_format($metrics['upcoming_events']) }}</td>
                        <td class="px-4 py-4 text-sm text-slate-700 dark:text-slate-200">{{ number_format($metrics['engagement_rate'], 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-6 text-center text-sm text-slate-500 dark:text-slate-300">
                            {{ __('No groups match the selected filters right now. Adjust the search to rediscover communities.') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Pagination control keeps navigation accessible for screen readers. --}}
        <div class="border-t border-slate-200 bg-slate-50 px-4 py-3 dark:border-slate-700 dark:bg-slate-900/60">
            {{ $groups->links() }}
        </div>
    </section>
</div>
