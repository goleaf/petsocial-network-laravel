<?php

namespace App\Http\Livewire\Account;

use App\Models\Comment;
use App\Models\Reaction;
use App\Models\Share;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Response;
use Illuminate\View\View;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Account analytics dashboard delivering personal engagement insights.
 */
class Analytics extends Component
{
    /**
     * High level snapshot metrics for the authenticated user.
     *
     * @var array<string, int>
     */
    public array $overview = [];

    /**
     * Date the analytics window should begin on.
     */
    public string $startDate = '';

    /**
     * Date the analytics window should end on.
     */
    public string $endDate = '';

    /**
     * Monthly engagement trend data for charts or tables.
     *
     * @var array<string, array<string, int>>
     */
    public array $engagementTrend = [];

    /**
     * Friend network metrics including growth and pending counts.
     *
     * @var array<string, int>
     */
    public array $friendInsights = [];

    /**
     * Detailed usage patterns for time-of-day and weekday analysis.
     *
     * @var array<string, array<string, int|float|string>>
     */
    public array $activityPatterns = [];

    /**
     * Ratios summarising how the account engages with the community.
     *
     * @var array<string, int|float>
     */
    public array $behaviorAnalysis = [];

    /**
     * Month over month growth snapshots for the member's audience.
     *
     * @var array<string, array<string, int>>
     */
    public array $growthTracking = [];

    /**
     * Snapshot of the custom report built for the selected range.
     *
     * @var array<string, int|float>
     */
    public array $reportSummary = [];

    /**
     * Collection of the user's top performing posts with reaction counts.
     */
    public Collection $topPosts;

    /**
     * Prepare the analytics data upon component mount.
     */
    public function mount(): void
    {
        /** @var User|null $user */
        $user = auth()->user();

        if ($user === null || (! $user->hasPermission('analytics.view') && ! $user->hasPermission('analytics.view_self'))) {
            abort(403, __('auth.unauthorized'));
        }

        $this->topPosts = collect();
        $this->startDate = Carbon::now()->subDays(29)->toDateString();
        $this->endDate = Carbon::now()->toDateString();

        $this->loadAnalytics($user);
    }

    /**
     * Build all analytics datasets for the provided user.
     */
    protected function loadAnalytics(User $user): void
    {
        [$start, $end] = $this->resolveDateRange();

        $this->overview = $this->buildOverview($user, $start, $end);
        $this->engagementTrend = $this->buildEngagementTrend($user, $start, $end);
        $this->friendInsights = $this->buildFriendInsights($user);
        $this->activityPatterns = $this->buildActivityPatterns($user, $start, $end);
        $this->behaviorAnalysis = $this->buildBehaviorAnalysis($user, $start, $end);
        $this->growthTracking = $this->buildGrowthTracking($user, $start, $end);
        $this->topPosts = $user->posts()
            ->withCount('reactions')
            ->withCount('comments')
            ->withCount('shares')
            ->orderByDesc('reactions_count')
            ->limit(5)
            ->get();
        $this->reportSummary = $this->buildReportSummary($user, $start, $end, $this->topPosts);
    }

    /**
     * Summarise total content and engagement metrics for the account.
     */
    protected function buildOverview(User $user, Carbon $start, Carbon $end): array
    {
        $range = $this->rangeBounds($start, $end);

        $receivedReactions = Reaction::whereHas('post', static function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
            ->where('user_id', '!=', $user->id)
            ->whereBetween('created_at', $range)
            ->count();

        $shares = Share::whereHas('post', static function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
            ->where('user_id', '!=', $user->id)
            ->whereBetween('created_at', $range)
            ->count();

        return [
            'posts' => $user->posts()->whereBetween('created_at', $range)->count(),
            'comments' => $user->comments()->whereBetween('created_at', $range)->count(),
            'reactions_made' => $user->reactions()->whereBetween('created_at', $range)->count(),
            'reactions_received' => $receivedReactions,
            'shares_received' => $shares,
        ];
    }

    /**
     * Assemble month-by-month engagement information for the last six months.
     */
    protected function buildEngagementTrend(User $user, Carbon $start, Carbon $end): array
    {
        $rangeStart = $start->copy()->startOfMonth();
        $rangeEnd = $end->copy()->startOfMonth();
        $period = CarbonPeriod::create($rangeStart, '1 month', $rangeEnd);
        $range = $this->rangeBounds($start, $end);

        $trend = [];

        foreach ($period as $month) {
            $label = $month->format('M Y');
            $trend[$label] = [
                'posts' => 0,
                'reactions_received' => 0,
                'shares' => 0,
            ];
        }

        $user->posts()
            ->whereBetween('created_at', $range)
            ->get(['created_at'])
            ->each(static function ($post) use (&$trend) {
                $label = $post->created_at->copy()->startOfMonth()->format('M Y');
                if (isset($trend[$label])) {
                    $trend[$label]['posts']++;
                }
            });

        Reaction::whereHas('post', static function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
            ->where('user_id', '!=', $user->id)
            ->whereBetween('created_at', $range)
            ->get(['created_at'])
            ->each(static function ($reaction) use (&$trend) {
                $label = $reaction->created_at->copy()->startOfMonth()->format('M Y');
                if (isset($trend[$label])) {
                    $trend[$label]['reactions_received']++;
                }
            });

        Share::whereHas('post', static function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
            ->where('user_id', '!=', $user->id)
            ->whereBetween('created_at', $range)
            ->get(['created_at'])
            ->each(static function ($share) use (&$trend) {
                $label = $share->created_at->copy()->startOfMonth()->format('M Y');
                if (isset($trend[$label])) {
                    $trend[$label]['shares']++;
                }
            });

        return $trend;
    }

    /**
     * Generate insights about the user's friend network health.
     */
    protected function buildFriendInsights(User $user): array
    {
        $accepted = $user->getAcceptedFriendships();
        $window = Carbon::now()->subDays(30);

        $newFriends = $accepted->filter(static function ($friendship) use ($window) {
            $acceptedAt = $friendship->accepted_at ?? $friendship->created_at;

            return $acceptedAt !== null && Carbon::parse($acceptedAt)->greaterThanOrEqualTo($window);
        })->count();

        return [
            'total' => $accepted->count(),
            'new_last_30_days' => $newFriends,
            'pending' => $user->getPendingFriendships()->count(),
            'blocked' => $user->getBlockedFriendships()->count(),
        ];
    }

    /**
     * Create daily and hourly usage pattern datasets for reporting.
     */
    protected function buildActivityPatterns(User $user, Carbon $start, Carbon $end): array
    {
        $range = $this->rangeBounds($start, $end);

        $posts = $user->posts()->whereBetween('created_at', $range)->get(['created_at']);
        $comments = Comment::where('user_id', $user->id)->whereBetween('created_at', $range)->get(['created_at']);
        $reactions = Reaction::where('user_id', $user->id)->whereBetween('created_at', $range)->get(['created_at']);
        $shares = Share::where('user_id', $user->id)->whereBetween('created_at', $range)->get(['created_at']);

        $weekdayKeys = [];
        foreach (Carbon::getDays() as $day) {
            $weekdayKeys[ucfirst($day)] = 0;
        }

        $byWeekday = $weekdayKeys;
        $byHour = array_fill(0, 24, 0);

        $posts->merge($comments)->merge($reactions)->merge($shares)->each(static function ($item) use (&$byWeekday, &$byHour) {
            $weekday = ucfirst($item->created_at->format('l'));
            if (array_key_exists($weekday, $byWeekday)) {
                $byWeekday[$weekday]++;
            }

            $hour = (int) $item->created_at->format('G');
            if (array_key_exists($hour, $byHour)) {
                $byHour[$hour]++;
            }
        });

        $peakHourValue = max($byHour);
        $peakHour = array_keys($byHour, $peakHourValue, true)[0] ?? 0;

        return [
            'by_weekday' => $byWeekday,
            'by_hour' => $byHour,
            'peak_hour_label' => sprintf('%02d:00', $peakHour),
        ];
    }

    /**
     * Evaluate interaction ratios that highlight behavioural preferences.
     */
    protected function buildBehaviorAnalysis(User $user, Carbon $start, Carbon $end): array
    {
        $range = $this->rangeBounds($start, $end);

        $postCount = $user->posts()->whereBetween('created_at', $range)->count();
        $reactionCount = Reaction::where('user_id', $user->id)->whereBetween('created_at', $range)->count();
        $commentCount = Comment::where('user_id', $user->id)->whereBetween('created_at', $range)->count();
        $receivedReactions = Reaction::whereHas('post', static function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
            ->where('user_id', '!=', $user->id)
            ->whereBetween('created_at', $range)
            ->count();
        $shareCount = Share::where('user_id', $user->id)->whereBetween('created_at', $range)->count();

        $activeDays = $user->posts()
            ->whereBetween('created_at', $range)
            ->get(['created_at'])
            ->pluck('created_at')
            ->map(static function ($date) {
                return $date->format('Y-m-d');
            })
            ->unique()
            ->count();

        return [
            'avg_posts_per_day' => $activeDays > 0 ? round($postCount / $activeDays, 2) : 0.0,
            'reactions_given_per_post' => $postCount > 0 ? round($reactionCount / $postCount, 2) : 0.0,
            'comments_per_post' => $postCount > 0 ? round($commentCount / $postCount, 2) : 0.0,
            'reactions_received_per_post' => $postCount > 0 ? round($receivedReactions / $postCount, 2) : 0.0,
            'shares_per_post' => $postCount > 0 ? round($shareCount / $postCount, 2) : 0.0,
        ];
    }

    /**
     * Track how the member's relationships evolve throughout the range.
     */
    protected function buildGrowthTracking(User $user, Carbon $start, Carbon $end): array
    {
        $rangeStart = $start->copy()->startOfMonth();
        $rangeEnd = $end->copy()->startOfMonth();
        $period = CarbonPeriod::create($rangeStart, '1 month', $rangeEnd);

        $growth = [];

        foreach ($period as $month) {
            $label = $month->format('M Y');
            $growth[$label] = [
                'new_friends' => 0,
                'new_followers' => 0,
            ];
        }

        $user->getAcceptedFriendships()->each(static function ($friendship) use (&$growth) {
            $acceptedAt = $friendship->accepted_at ?? $friendship->created_at;
            if ($acceptedAt === null) {
                return;
            }

            $label = Carbon::parse($acceptedAt)->startOfMonth()->format('M Y');
            if (isset($growth[$label])) {
                $growth[$label]['new_friends']++;
            }
        });

        if (method_exists($user, 'followers')) {
            $user->followers()
                ->wherePivotBetween('created_at', [$start->copy(), $end->copy()])
                ->get()
                ->each(static function ($follower) use (&$growth) {
                    $label = Carbon::parse($follower->pivot->created_at)->startOfMonth()->format('M Y');
                    if (isset($growth[$label])) {
                        $growth[$label]['new_followers']++;
                    }
                });
        }

        return $growth;
    }

    /**
     * Provide a consolidated data snapshot for custom reporting.
     */
    protected function buildReportSummary(User $user, Carbon $start, Carbon $end, Collection $topPosts): array
    {
        $overview = $this->buildOverview($user, $start, $end);

        return array_merge($overview, [
            'date_range_days' => $start->diffInDays($end) + 1,
            'top_post_reactions' => $topPosts->first()->reactions_count ?? 0,
        ]);
    }

    /**
     * Export the analytics dataset as a CSV attachment for offline review.
     */
    public function exportReport(): StreamedResponse
    {
        /** @var User|null $user */
        $user = auth()->user();

        if ($user === null) {
            abort(403, __('auth.unauthorized'));
        }

        [$start, $end] = $this->resolveDateRange();
        $summary = $this->buildReportSummary($user, $start, $end, $this->topPosts);

        $headers = [
            'Content-Type' => 'text/csv',
        ];

        return Response::streamDownload(static function () use ($summary) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Metric', 'Value']);

            foreach ($summary as $key => $value) {
                fputcsv($handle, [$key, $value]);
            }

            fclose($handle);
        }, 'account-analytics-report.csv', $headers);
    }

    /**
     * Refresh analytics whenever a filter value changes.
     */
    public function refreshAnalytics(): void
    {
        /** @var User|null $user */
        $user = auth()->user();

        if ($user === null) {
            abort(403, __('auth.unauthorized'));
        }

        $this->loadAnalytics($user);
    }

    /**
     * React to manual adjustments of the start date filter.
     */
    public function updatedStartDate(): void
    {
        $this->refreshAnalytics();
    }

    /**
     * React to manual adjustments of the end date filter.
     */
    public function updatedEndDate(): void
    {
        $this->refreshAnalytics();
    }

    /**
     * Resolve a valid Carbon date range from the current filter state.
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    protected function resolveDateRange(): array
    {
        $defaultEnd = Carbon::now()->endOfDay();
        $end = $this->parseDate($this->endDate, $defaultEnd)->endOfDay();
        $defaultStart = $end->copy()->subDays(29)->startOfDay();
        $start = $this->parseDate($this->startDate, $defaultStart)->startOfDay();

        if ($start->greaterThan($end)) {
            $start = $defaultStart;
            $this->startDate = $start->toDateString();
            $this->endDate = $end->toDateString();
        }

        return [$start, $end];
    }

    /**
     * Parse a provided date string or fall back to a default value.
     */
    protected function parseDate(?string $value, Carbon $fallback): Carbon
    {
        if ($value === null || $value === '') {
            return $fallback;
        }

        try {
            return Carbon::createFromFormat('Y-m-d', $value);
        } catch (\Throwable) {
            return $fallback;
        }
    }

    /**
     * Convert the active date range to string bounds for database comparisons.
     *
     * @return array{0: string, 1: string}
     */
    protected function rangeBounds(Carbon $start, Carbon $end): array
    {
        return [$start->copy()->toDateTimeString(), $end->copy()->toDateTimeString()];
    }

    /**
     * Render the analytics dashboard view.
     */
    public function render(): View
    {
        return view('livewire.account.analytics')->layout('layouts.app');
    }
}
