<x-app-layout>
    <x-slot name="header">
        <div class="space-y-1">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('account.title') }}
            </h2>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                {{ __('account.description') }}
            </p>
        </div>
    </x-slot>

    @php
        use Illuminate\Support\Str;
    @endphp

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Engagement summary section -->
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                <div class="p-6 space-y-6">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                            {{ __('account.engagement_summary') }}
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            {{ __('account.engagement_summary_help') }}
                        </p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div class="p-4 rounded-lg bg-indigo-50 dark:bg-indigo-900/30">
                            <p class="text-sm text-indigo-600 dark:text-indigo-300">
                                {{ __('account.total_posts') }}
                            </p>
                            <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-gray-100">
                                {{ number_format($engagement['total_posts']) }}
                            </p>
                        </div>
                        <div class="p-4 rounded-lg bg-emerald-50 dark:bg-emerald-900/30">
                            <p class="text-sm text-emerald-600 dark:text-emerald-300">
                                {{ __('account.overall_interactions') }}
                            </p>
                            <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-gray-100">
                                {{ number_format($engagement['overall_interactions']) }}
                            </p>
                        </div>
                        <div class="p-4 rounded-lg bg-amber-50 dark:bg-amber-900/30">
                            <p class="text-sm text-amber-600 dark:text-amber-300">
                                {{ __('account.avg_engagement_per_post') }}
                            </p>
                            <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-gray-100">
                                {{ number_format($engagement['average_per_post'], 1) }}
                            </p>
                        </div>
                    </div>

                    <div>
                        <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wide">
                            {{ __('account.last_30_days') }}
                        </h4>
                        <div class="mt-3 grid grid-cols-2 md:grid-cols-5 gap-3">
                            <div class="p-3 border border-gray-200 dark:border-gray-700 rounded-lg">
                                <p class="text-xs text-gray-500 dark:text-gray-400 uppercase">
                                    {{ __('account.last_30_days_posts') }}
                                </p>
                                <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-100">
                                    {{ number_format($engagement['last_30_days']['posts']) }}
                                </p>
                            </div>
                            <div class="p-3 border border-gray-200 dark:border-gray-700 rounded-lg">
                                <p class="text-xs text-gray-500 dark:text-gray-400 uppercase">
                                    {{ __('account.last_30_days_comments') }}
                                </p>
                                <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-100">
                                    {{ number_format($engagement['last_30_days']['comments']) }}
                                </p>
                            </div>
                            <div class="p-3 border border-gray-200 dark:border-gray-700 rounded-lg">
                                <p class="text-xs text-gray-500 dark:text-gray-400 uppercase">
                                    {{ __('account.last_30_days_reactions') }}
                                </p>
                                <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-100">
                                    {{ number_format($engagement['last_30_days']['reactions']) }}
                                </p>
                            </div>
                            <div class="p-3 border border-gray-200 dark:border-gray-700 rounded-lg">
                                <p class="text-xs text-gray-500 dark:text-gray-400 uppercase">
                                    {{ __('account.last_30_days_shares') }}
                                </p>
                                <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-100">
                                    {{ number_format($engagement['last_30_days']['shares']) }}
                                </p>
                            </div>
                            <div class="p-3 border border-gray-200 dark:border-gray-700 rounded-lg">
                                <p class="text-xs text-gray-500 dark:text-gray-400 uppercase">
                                    {{ __('account.last_30_days_average') }}
                                </p>
                                <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-100">
                                    {{ number_format($engagement['last_30_days']['average'], 1) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Engagement trend table -->
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                {{ __('account.engagement_trend') }}
                            </h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                {{ __('account.engagement_trend_help') }}
                            </p>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th scope="col" class="px-4 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        {{ __('account.trend_period') }}
                                    </th>
                                    <th scope="col" class="px-4 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        {{ __('account.trend_posts') }}
                                    </th>
                                    <th scope="col" class="px-4 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        {{ __('account.trend_comments') }}
                                    </th>
                                    <th scope="col" class="px-4 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        {{ __('account.trend_reactions') }}
                                    </th>
                                    <th scope="col" class="px-4 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        {{ __('account.trend_shares') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($trendData as $trend)
                                    <tr>
                                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                            {{ $trend['label'] }}
                                        </td>
                                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                            {{ number_format($trend['posts']) }}
                                        </td>
                                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                            {{ number_format($trend['comments']) }}
                                        </td>
                                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                            {{ number_format($trend['reactions']) }}
                                        </td>
                                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                            {{ number_format($trend['shares']) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Friend statistics section -->
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                <div class="p-6 space-y-6">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                            {{ __('account.friend_statistics') }}
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            {{ __('account.friend_statistics_help') }}
                        </p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                        <div class="p-4 rounded-lg bg-sky-50 dark:bg-sky-900/30">
                            <p class="text-sm text-sky-600 dark:text-sky-300">
                                {{ __('account.total_friends') }}
                            </p>
                            <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-gray-100">
                                {{ number_format($friendStats['total_friends']) }}
                            </p>
                        </div>
                        <div class="p-4 rounded-lg bg-purple-50 dark:bg-purple-900/30">
                            <p class="text-sm text-purple-600 dark:text-purple-300">
                                {{ __('account.new_friends_last_30_days') }}
                            </p>
                            <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-gray-100">
                                {{ number_format($friendStats['new_friends_last_30_days']) }}
                            </p>
                        </div>
                        <div class="p-4 rounded-lg bg-rose-50 dark:bg-rose-900/30">
                            <p class="text-sm text-rose-600 dark:text-rose-300">
                                {{ __('account.pending_sent') }}
                            </p>
                            <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-gray-100">
                                {{ number_format($friendStats['pending_sent']) }}
                            </p>
                        </div>
                        <div class="p-4 rounded-lg bg-slate-50 dark:bg-slate-900/30">
                            <p class="text-sm text-slate-600 dark:text-slate-300">
                                {{ __('account.pending_received') }}
                            </p>
                            <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-gray-100">
                                {{ number_format($friendStats['pending_received']) }}
                            </p>
                        </div>
                        <div class="p-4 rounded-lg bg-orange-50 dark:bg-orange-900/30">
                            <p class="text-sm text-orange-600 dark:text-orange-300">
                                {{ __('account.blocked_friends') }}
                            </p>
                            <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-gray-100">
                                {{ number_format($friendStats['blocked']) }}
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div class="p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
                            <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wide">
                                {{ __('account.friends_by_category') }}
                            </h4>
                            <div class="mt-3 space-y-3">
                                @if($friendStats['category_breakdown']->isEmpty())
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ __('account.no_category_data') }}
                                    </p>
                                @else
                                    @foreach($friendStats['category_breakdown'] as $category)
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm text-gray-700 dark:text-gray-300">
                                                {{ $category['category'] ?? __('account.uncategorized') }}
                                            </span>
                                            <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                                {{ number_format($category['total']) }}
                                            </span>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>

                        <div class="p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
                            <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wide">
                                {{ __('account.recent_friend_connections') }}
                            </h4>
                            <div class="mt-3 space-y-3">
                                @if($friendStats['recent_connections']->isEmpty())
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ __('account.no_recent_friend_connections') }}
                                    </p>
                                @else
                                    @foreach($friendStats['recent_connections'] as $connection)
                                        <div>
                                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                {{ $connection['name'] }}
                                            </p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ $connection['since'] ? __('account.connected_time', ['time' => $connection['since']]) : '—' }}
                                            </p>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content performance section -->
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                <div class="p-6 space-y-6">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                            {{ __('account.content_performance') }}
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            {{ __('account.content_performance_help') }}
                        </p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="p-4 rounded-lg bg-cyan-50 dark:bg-cyan-900/30">
                            <p class="text-sm text-cyan-600 dark:text-cyan-300">
                                {{ __('account.recent_average_engagement') }}
                            </p>
                            <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-gray-100">
                                {{ number_format($contentPerformance['recent_average_engagement'], 1) }}
                            </p>
                        </div>
                        <div class="p-4 rounded-lg bg-lime-50 dark:bg-lime-900/30">
                            <p class="text-sm text-lime-600 dark:text-lime-300">
                                {{ __('account.recent_posting_frequency') }}
                            </p>
                            <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-gray-100">
                                {{ number_format($contentPerformance['recent_posting_frequency'], 1) }}
                            </p>
                        </div>
                        <div class="p-4 rounded-lg bg-fuchsia-50 dark:bg-fuchsia-900/30">
                            <p class="text-sm text-fuchsia-600 dark:text-fuchsia-300">
                                {{ __('account.overall_interactions') }}
                            </p>
                            <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-gray-100">
                                {{ number_format($contentPerformance['overall_interactions']) }}
                            </p>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wide mb-3">
                            {{ __('account.top_posts') }}
                        </h4>
                        @if($contentPerformance['top_posts']->isEmpty())
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                {{ __('account.no_posts_available') }}
                            </p>
                        @else
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th scope="col" class="px-4 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            {{ __('account.post_excerpt') }}
                                        </th>
                                        <th scope="col" class="px-4 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            {{ __('account.post_published') }}
                                        </th>
                                        <th scope="col" class="px-4 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            {{ __('account.engagement_score') }}
                                        </th>
                                        <th scope="col" class="px-4 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            {{ __('account.comments_label') }}
                                        </th>
                                        <th scope="col" class="px-4 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            {{ __('account.reactions_label') }}
                                        </th>
                                        <th scope="col" class="px-4 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            {{ __('account.shares_label') }}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($contentPerformance['top_posts'] as $post)
                                        <tr>
                                            <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300">
                                                {{ Str::limit($post['content'], 80) }}
                                            </td>
                                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                {{ optional($post['created_at'])->format('M d, Y') }}
                                            </td>
                                            <td class="px-4 py-2 whitespace-nowrap text-sm font-semibold text-gray-900 dark:text-gray-100">
                                                {{ number_format($post['engagement_score']) }}
                                            </td>
                                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                                {{ number_format($post['comments']) }}
                                            </td>
                                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                                {{ number_format($post['reactions']) }}
                                            </td>
                                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                                {{ number_format($post['shares']) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
