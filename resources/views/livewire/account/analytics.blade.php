<div class="max-w-6xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    {{-- Overview cards provide a quick summary of personal engagement metrics. --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-5">
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('common.analytics_total_posts') }}</p>
            <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $overview['posts'] ?? 0 }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-5">
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('common.analytics_total_comments') }}</p>
            <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $overview['comments'] ?? 0 }}</p>
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
                </li>
            @empty
                <li class="text-sm text-gray-500 dark:text-gray-400">{{ __('common.analytics_top_posts_empty') }}</li>
            @endforelse
        </ul>
    </div>
</div>
