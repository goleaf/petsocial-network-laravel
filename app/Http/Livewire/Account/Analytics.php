<?php

namespace App\Http\Livewire\Account;

use App\Models\Reaction;
use App\Models\Share;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Livewire\Component;

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

        $this->loadAnalytics($user);
    }

    /**
     * Build all analytics datasets for the provided user.
     */
    protected function loadAnalytics(User $user): void
    {
        $this->overview = $this->buildOverview($user);
        $this->engagementTrend = $this->buildEngagementTrend($user);
        $this->friendInsights = $this->buildFriendInsights($user);
        $this->topPosts = $user->posts()
            ->withCount('reactions')
            ->orderByDesc('reactions_count')
            ->limit(5)
            ->get();
    }

    /**
     * Summarise total content and engagement metrics for the account.
     */
    protected function buildOverview(User $user): array
    {
        $receivedReactions = Reaction::whereHas('post', static function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->count();

        $shares = Share::whereHas('post', static function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->count();

        return [
            'posts' => $user->posts()->count(),
            'comments' => $user->comments()->count(),
            'reactions_made' => $user->reactions()->count(),
            'reactions_received' => $receivedReactions,
            'shares_received' => $shares,
        ];
    }

    /**
     * Assemble month-by-month engagement information for the last six months.
     */
    protected function buildEngagementTrend(User $user): array
    {
        $months = 6;
        $start = Carbon::now()->startOfMonth()->subMonths($months - 1);
        $period = CarbonPeriod::create($start, '1 month', Carbon::now()->startOfMonth());

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
            ->where('created_at', '>=', $start)
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
            ->where('created_at', '>=', $start)
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
            ->where('created_at', '>=', $start)
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
     * Render the analytics dashboard view.
     */
    public function render()
    {
        return view('livewire.account.analytics')->layout('layouts.app');
    }
}
