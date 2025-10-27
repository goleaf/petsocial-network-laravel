<div class="max-w-6xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    {{-- Date range filters give members control over the analytics window. --}}
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
        <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('common.analytics_filters_title') }}</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('common.analytics_filters_description') }}</p>
            </div>
            <div class="flex flex-col sm:flex-row gap-4">
                <label class="flex flex-col text-sm text-gray-700 dark:text-gray-300">
                    <span class="mb-1 font-medium">{{ __('common.analytics_filters_start') }}</span>
                    <input wire:model.lazy="startDate" type="date" class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500" />
                </label>
                <label class="flex flex-col text-sm text-gray-700 dark:text-gray-300">
                    <span class="mb-1 font-medium">{{ __('common.analytics_filters_end') }}</span>
                    <input wire:model.lazy="endDate" type="date" class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500" />
                </label>
                <button type="button" wire:click="exportReport" class="inline-flex items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-gray-100 dark:focus:ring-offset-gray-900">{{ __('common.analytics_export_button') }}</button>
            </div>
        </div>
    </div>

    {{-- Overview cards provide a quick summary of personal engagement metrics. --}}
    <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-6">
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-5">
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('common.analytics_total_posts') }}</p>
            <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $overview['posts'] ?? 0 }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-5">
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('common.analytics_total_comments') }}</p>
            <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $overview['comments'] ?? 0 }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-5">
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('common.analytics_reactions_made') }}</p>
            <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $overview['reactions_made'] ?? 0 }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-5">
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('common.analytics_reactions_received') }}</p>
            <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $overview['reactions_received'] ?? 0 }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-5">
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('common.analytics_shares_received') }}</p>
            <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $overview['shares_received'] ?? 0 }}</p>
        </div>
    </div>

    {{-- Friend insights summarise network health and moderation posture. --}}
    <div class="mt-10 bg-white dark:bg-gray-800 shadow rounded-lg p-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('common.analytics_friend_insights_title') }}</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __('common.analytics_friend_insights_description') }}</p>

        <dl class="mt-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="flex flex-col">
                <dt class="text-sm text-gray-500 dark:text-gray-400">{{ __('common.analytics_total_friends') }}</dt>
                <dd class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ $friendInsights['total'] ?? 0 }}</dd>
            </div>
            <div class="flex flex-col">
                <dt class="text-sm text-gray-500 dark:text-gray-400">{{ __('common.analytics_new_friends_last_30_days') }}</dt>
                <dd class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ $friendInsights['new_last_30_days'] ?? 0 }}</dd>
            </div>
            <div class="flex flex-col">
                <dt class="text-sm text-gray-500 dark:text-gray-400">{{ __('common.analytics_pending_requests') }}</dt>
                <dd class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ $friendInsights['pending'] ?? 0 }}</dd>
            </div>
            <div class="flex flex-col">
                <dt class="text-sm text-gray-500 dark:text-gray-400">{{ __('common.analytics_blocked_connections') }}</dt>
                <dd class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ $friendInsights['blocked'] ?? 0 }}</dd>
            </div>
        </dl>
    </div>

    {{-- Trend table illustrates engagement momentum without requiring charts. --}}
    <div class="mt-10 bg-white dark:bg-gray-800 shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('common.analytics_engagement_trend_title') }}</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('common.analytics_engagement_trend_description') }}</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('common.analytics_month') }}</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('common.analytics_posts_created') }}</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('common.analytics_reactions_received') }}</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('common.analytics_shares_received') }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($engagementTrend as $period => $data)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $period }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">{{ $data['posts'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">{{ $data['reactions_received'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">{{ $data['shares'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ __('common.analytics_trend_empty') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Highlight the top performing posts to encourage continued engagement. --}}
    <div class="mt-10 bg-white dark:bg-gray-800 shadow rounded-lg p-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('common.analytics_top_posts_title') }}</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('common.analytics_top_posts_description') }}</p>

        <ul class="mt-6 space-y-4">
            @forelse($topPosts as $post)
                <li class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                    <div class="flex justify-between items-start">
                        <p class="text-gray-800 dark:text-gray-100">{{ \Illuminate\Support\Str::limit($post->content, 140) }}</p>
                        <span class="ml-4 inline-flex items-center px-3 py-1 rounded-full text-sm bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200">{{ trans_choice('common.analytics_reactions_count', $post->reactions_count, ['count' => $post->reactions_count]) }}</span>
                    </div>
                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">{{ __('common.analytics_post_published_at', ['date' => optional($post->created_at)->format('M j, Y')]) }}</p>
                    <dl class="mt-3 grid grid-cols-1 sm:grid-cols-3 gap-2 text-xs text-gray-600 dark:text-gray-300">
                        <div class="flex items-center justify-between sm:flex-col sm:items-start sm:justify-start">
                            <dt class="font-medium">{{ __('common.analytics_top_post_comments') }}</dt>
                            <dd>{{ $post->comments_count }}</dd>
                        </div>
                        <div class="flex items-center justify-between sm:flex-col sm:items-start sm:justify-start">
                            <dt class="font-medium">{{ __('common.analytics_top_post_shares') }}</dt>
                            <dd>{{ $post->shares_count }}</dd>
                        </div>
                        <div class="flex items-center justify-between sm:flex-col sm:items-start sm:justify-start">
                            <dt class="font-medium">{{ __('common.analytics_top_post_total_engagement') }}</dt>
                            <dd>{{ $post->reactions_count + $post->comments_count + $post->shares_count }}</dd>
                        </div>
                    </dl>
                </li>
            @empty
                <li class="text-sm text-gray-500 dark:text-gray-400">{{ __('common.analytics_top_posts_empty') }}</li>
            @endforelse
        </ul>
    </div>

    {{-- Activity patterns visualise peak usage periods without external tooling. --}}
    <div class="mt-10 bg-white dark:bg-gray-800 shadow rounded-lg p-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('common.analytics_activity_patterns_title') }}</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('common.analytics_activity_patterns_description') }}</p>

        <div class="mt-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div>
                <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ __('common.analytics_activity_weekdays') }}</h3>
                <ul class="mt-3 space-y-2">
                    @foreach($activityPatterns['by_weekday'] ?? [] as $weekday => $count)
                        <li class="flex items-center justify-between rounded-md border border-gray-200 dark:border-gray-700 px-4 py-2 text-sm text-gray-700 dark:text-gray-300">
                            <span>{{ $weekday }}</span>
                            <span class="font-semibold">{{ $count }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ __('common.analytics_activity_hours') }}</h3>
                <ul class="mt-3 space-y-2 max-h-64 overflow-y-auto pr-1">
                    @foreach($activityPatterns['by_hour'] ?? [] as $hour => $count)
                        <li class="flex items-center justify-between rounded-md border border-gray-200 dark:border-gray-700 px-4 py-2 text-sm text-gray-700 dark:text-gray-300">
                            <span>{{ sprintf('%02d:00', $hour) }}</span>
                            <span class="font-semibold">{{ $count }}</span>
                        </li>
                    @endforeach
                </ul>
                <p class="mt-4 text-xs text-gray-500 dark:text-gray-400">{{ __('common.analytics_activity_peak', ['time' => $activityPatterns['peak_hour_label'] ?? '00:00']) }}</p>
            </div>
        </div>
    </div>

    {{-- Behaviour analysis surfaces engagement ratios for quick interpretation. --}}
    <div class="mt-10 bg-white dark:bg-gray-800 shadow rounded-lg p-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('common.analytics_behavior_title') }}</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('common.analytics_behavior_description') }}</p>

        <dl class="mt-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="flex flex-col">
                <dt class="text-sm text-gray-500 dark:text-gray-400">{{ __('common.analytics_behavior_avg_posts_per_day') }}</dt>
                <dd class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ $behaviorAnalysis['avg_posts_per_day'] ?? 0 }}</dd>
            </div>
            <div class="flex flex-col">
                <dt class="text-sm text-gray-500 dark:text-gray-400">{{ __('common.analytics_behavior_reactions_per_post') }}</dt>
                <dd class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ $behaviorAnalysis['reactions_given_per_post'] ?? 0 }}</dd>
            </div>
            <div class="flex flex-col">
                <dt class="text-sm text-gray-500 dark:text-gray-400">{{ __('common.analytics_behavior_comments_per_post') }}</dt>
                <dd class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ $behaviorAnalysis['comments_per_post'] ?? 0 }}</dd>
            </div>
            <div class="flex flex-col">
                <dt class="text-sm text-gray-500 dark:text-gray-400">{{ __('common.analytics_behavior_received_per_post') }}</dt>
                <dd class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ $behaviorAnalysis['reactions_received_per_post'] ?? 0 }}</dd>
            </div>
            <div class="flex flex-col">
                <dt class="text-sm text-gray-500 dark:text-gray-400">{{ __('common.analytics_behavior_shares_per_post') }}</dt>
                <dd class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ $behaviorAnalysis['shares_per_post'] ?? 0 }}</dd>
            </div>
        </dl>
    </div>

    {{-- Growth tracking illustrates how the audience changes over time. --}}
    <div class="mt-10 bg-white dark:bg-gray-800 shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('common.analytics_growth_title') }}</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('common.analytics_growth_description') }}</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('common.analytics_month') }}</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('common.analytics_growth_new_friends') }}</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('common.analytics_growth_new_followers') }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($growthTracking as $period => $data)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $period }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">{{ $data['new_friends'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">{{ $data['new_followers'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ __('common.analytics_growth_empty') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Custom report summary highlights export contents for quick scanning. --}}
    <div class="mt-10 bg-white dark:bg-gray-800 shadow rounded-lg p-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('common.analytics_report_summary_title') }}</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('common.analytics_report_summary_description') }}</p>

        <dl class="mt-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($reportSummary as $metric => $value)
                @php
                    $translationKey = 'common.analytics_report_summary_metric_' . $metric;
                    $label = __($translationKey);
                    if ($label === $translationKey) {
                        $label = \Illuminate\Support\Str::headline(str_replace('_', ' ', $metric));
                    }
                @endphp
                <div class="flex flex-col rounded-md border border-gray-200 dark:border-gray-700 p-4">
                    <dt class="text-sm text-gray-500 dark:text-gray-400">{{ $label }}</dt>
                    <dd class="mt-2 text-xl font-semibold text-gray-900 dark:text-gray-100">{{ $value }}</dd>
                </div>
            @endforeach
        </dl>
        <p class="mt-4 text-xs text-gray-500 dark:text-gray-400">{{ __('common.analytics_report_summary_hint') }}</p>
    </div>
</div>
