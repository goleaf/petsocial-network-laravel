<?php

namespace App\Http\Livewire\Common\Friend;

use App\Models\Friendship;
use App\Models\PetFriendship;
use App\Traits\EntityTypeTrait;
use App\Traits\FriendshipTrait;
use Carbon\Carbon;
use Livewire\Component;

class Analytics extends Component
{
    use EntityTypeTrait, FriendshipTrait;

    /**
     * Summary statistics for the analytics view.
     *
     * @var array
     */
    public $summary = [];

    /**
     * Monthly connection trend data keyed by period label.
     *
     * @var array
     */
    public $trendData = [];

    /**
     * Category breakdown counts keyed by category label.
     *
     * @var array
     */
    public $categoryBreakdown = [];

    /**
     * Insights about mutual connections.
     *
     * @var array
     */
    public $mutualInsights = [];

    /**
     * Selected timeframe for the trend visualisation.
     *
     * @var string
     */
    public $trendRange = '6_months';

    /**
     * Available timeframe options and their month lengths.
     *
     * @var array<string, int>
     */
    protected $trendOptions = [
        '3_months' => 3,
        '6_months' => 6,
        '12_months' => 12,
    ];

    /**
     * Mount the analytics component with the provided entity context.
     *
     * @param string $entityType
     * @param int|null $entityId
     * @return void
     */
    public function mount(string $entityType = 'user', ?int $entityId = null): void
    {
        // Determine the entity ID, defaulting to the authenticated user for user analytics.
        $resolvedId = $entityId ?? ($entityType === 'user' ? auth()->id() : null);

        if (!$resolvedId) {
            abort(404, __('friends.entity_id_required'));
        }

        // Initialise shared entity helpers.
        $this->initializeEntity($entityType, $resolvedId);

        // Prevent unauthorised users from accessing analytics for other entities.
        abort_if(!$this->isAuthorized(), 403, __('friends.authorization_required'));

        // Populate analytics when the component is mounted.
        $this->loadAnalytics();
    }

    /**
     * React to timeframe updates from the UI.
     *
     * @return void
     */
    public function updatedTrendRange(): void
    {
        $this->loadAnalytics();
    }

    /**
     * Load all analytics datasets in one pass.
     *
     * @return void
     */
    public function loadAnalytics(): void
    {
        $acceptedQuery = $this->getAcceptedFriendshipsQuery();

        $this->summary = $this->buildSummaryStatistics(clone $acceptedQuery);
        $this->categoryBreakdown = $this->buildCategoryBreakdown(clone $acceptedQuery);
        $this->trendData = $this->buildTrendData(clone $acceptedQuery);
        $this->mutualInsights = $this->buildMutualInsights();
    }

    /**
     * Build summary statistics that highlight the current relationship health.
     *
     * @param \Illuminate\Database\Eloquent\Builder $acceptedQuery
     * @return array
     */
    protected function buildSummaryStatistics($acceptedQuery): array
    {
        $thirtyDaysAgo = Carbon::now()->subDays(30);

        // Count how many accepted friendships exist for the entity.
        $totalFriends = (clone $acceptedQuery)->count();

        // Determine how many new friends were added in the last 30 days.
        $newFriends = (clone $acceptedQuery)
            ->where(function ($query) use ($thirtyDaysAgo) {
                $query->whereNotNull('accepted_at')->where('accepted_at', '>=', $thirtyDaysAgo)
                    ->orWhere(function ($subQuery) use ($thirtyDaysAgo) {
                        $subQuery->whereNull('accepted_at')->where('created_at', '>=', $thirtyDaysAgo);
                    });
            })
            ->count();

        // Pending requests from both perspectives.
        $pendingSent = $this->getPendingSentQuery()->count();
        $pendingReceived = $this->getPendingReceivedQuery()->count();

        // Count how many relationships are currently blocked.
        $blocked = $this->getBlockedFriendshipsQuery()->count();

        // Compute the average acceptance time in hours where data is available.
        $acceptanceDurations = (clone $acceptedQuery)
            ->whereNotNull('accepted_at')
            ->get(['accepted_at', 'created_at'])
            ->map(function ($friendship) {
                // Calculate the number of hours between request creation and acceptance.
                return $friendship->created_at
                    ? $friendship->created_at->diffInHours($friendship->accepted_at)
                    : null;
            })
            ->filter();

        $averageAcceptanceHours = $acceptanceDurations->isNotEmpty()
            ? round($acceptanceDurations->avg(), 1)
            : null;

        return [
            'total_friends' => $totalFriends,
            'new_friends_last_30_days' => $newFriends,
            'pending_sent' => $pendingSent,
            'pending_received' => $pendingReceived,
            'blocked' => $blocked,
            'average_acceptance_hours' => $averageAcceptanceHours,
        ];
    }

    /**
     * Build the category distribution for accepted friendships.
     *
     * @param \Illuminate\Database\Eloquent\Builder $acceptedQuery
     * @return array
     */
    protected function buildCategoryBreakdown($acceptedQuery): array
    {
        $categories = (clone $acceptedQuery)
            ->get(['category'])
            ->map(function ($friendship) {
                // Use a friendly label for uncategorised connections.
                return $friendship->category ?? __('friends.unknown');
            })
            ->countBy()
            ->sortKeys()
            ->toArray();

        return $categories;
    }

    /**
     * Build the trend data for the selected timeframe.
     *
     * @param \Illuminate\Database\Eloquent\Builder $acceptedQuery
     * @return array<string, int>
     */
    protected function buildTrendData($acceptedQuery): array
    {
        $months = $this->trendOptions[$this->trendRange] ?? $this->trendOptions['6_months'];
        $startPeriod = Carbon::now()->startOfMonth()->subMonths($months - 1);

        // Pre-fill the array so missing months render as zeros.
        $periodCursor = $startPeriod->copy();
        $trend = [];
        for ($i = 0; $i < $months; $i++) {
            $trend[$periodCursor->format('Y-m')] = 0;
            $periodCursor->addMonth();
        }

        $friendships = (clone $acceptedQuery)
            ->where(function ($query) use ($startPeriod) {
                $query->whereNotNull('accepted_at')->where('accepted_at', '>=', $startPeriod)
                    ->orWhere(function ($subQuery) use ($startPeriod) {
                        $subQuery->whereNull('accepted_at')->where('created_at', '>=', $startPeriod);
                    });
            })
            ->get(['accepted_at', 'created_at']);

        foreach ($friendships as $friendship) {
            $timestamp = $friendship->accepted_at ?? $friendship->created_at;
            if (!$timestamp) {
                continue;
            }

            $periodKey = $timestamp->copy()->format('Y-m');
            if (array_key_exists($periodKey, $trend)) {
                $trend[$periodKey]++;
            }
        }

        return $trend;
    }

    /**
     * Build insights about mutual connections to highlight strong ties.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function buildMutualInsights(): array
    {
        $friendIds = $this->getFriendIds();

        if (empty($friendIds)) {
            return [];
        }

        $topFriends = [];

        // Limit the workload by sampling the first ten friends.
        foreach (array_slice($friendIds, 0, 10) as $friendId) {
            $mutualCount = count($this->getMutualFriendIds($friendId));
            $topFriends[] = [
                'id' => $friendId,
                'mutual_count' => $mutualCount,
            ];
        }

        // Sort by mutual connections descending and keep the top five.
        usort($topFriends, fn($a, $b) => $b['mutual_count'] <=> $a['mutual_count']);
        $topFriends = array_slice($topFriends, 0, 5);

        if (empty($topFriends)) {
            return [];
        }

        $entityModel = $this->getEntityModel();
        $names = $entityModel::whereIn('id', array_column($topFriends, 'id'))
            ->get(['id', 'name'])
            ->keyBy('id');

        return array_map(function ($friend) use ($names) {
            return [
                'name' => optional($names->get($friend['id']))->name ?? __('friends.unknown'),
                'mutual_count' => $friend['mutual_count'],
            ];
        }, $topFriends);
    }

    /**
     * Retrieve a query builder scoped to accepted friendships for the entity.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function getAcceptedFriendshipsQuery()
    {
        $model = $this->getFriendshipModel();

        if ($model === Friendship::class) {
            return $model::query()
                ->where(function ($query) {
                    $query->where('sender_id', $this->entityId)
                        ->orWhere('recipient_id', $this->entityId);
                })
                ->where('status', Friendship::STATUS_ACCEPTED);
        }

        return $model::query()
            ->where(function ($query) {
                $query->where('pet_id', $this->entityId)
                    ->orWhere('friend_pet_id', $this->entityId);
            })
            ->where('status', PetFriendship::STATUS_ACCEPTED);
    }

    /**
     * Retrieve a query builder for pending requests initiated by the entity.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function getPendingSentQuery()
    {
        $model = $this->getFriendshipModel();

        if ($model === Friendship::class) {
            return $model::query()
                ->where('sender_id', $this->entityId)
                ->where('status', Friendship::STATUS_PENDING);
        }

        return $model::query()
            ->where('pet_id', $this->entityId)
            ->where('status', PetFriendship::STATUS_PENDING);
    }

    /**
     * Retrieve a query builder for pending requests received by the entity.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function getPendingReceivedQuery()
    {
        $model = $this->getFriendshipModel();

        if ($model === Friendship::class) {
            return $model::query()
                ->where('recipient_id', $this->entityId)
                ->where('status', Friendship::STATUS_PENDING);
        }

        return $model::query()
            ->where('friend_pet_id', $this->entityId)
            ->where('status', PetFriendship::STATUS_PENDING);
    }

    /**
     * Retrieve a query builder for blocked friendships that involve the entity.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function getBlockedFriendshipsQuery()
    {
        $model = $this->getFriendshipModel();

        if ($model === Friendship::class) {
            return $model::query()
                ->where(function ($query) {
                    $query->where('sender_id', $this->entityId)
                        ->orWhere('recipient_id', $this->entityId);
                })
                ->where('status', Friendship::STATUS_BLOCKED);
        }

        return $model::query()
            ->where(function ($query) {
                $query->where('pet_id', $this->entityId)
                    ->orWhere('friend_pet_id', $this->entityId);
            })
            ->where('status', PetFriendship::STATUS_BLOCKED);
    }

    /**
     * Render the analytics view for Livewire.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function render()
    {
        return view('livewire.common.friend.analytics');
    }
}
