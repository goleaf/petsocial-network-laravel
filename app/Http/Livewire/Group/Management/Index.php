<?php

namespace App\Http\Livewire\Group\Management;

use App\Models\Group\Category;
use App\Models\Group\Group;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Index extends Component
{
    use WithFileUploads, WithPagination;

    public $name;
    public $description;
    public $categoryId;
    public $visibility = 'open';
    public $location;
    public $coverImage;
    public $icon;
    public $groupRules = [];
    
    public $search = '';
    public $filter = 'all';
    public $showCreateModal = false;

    /**
     * Cached summary metrics that describe overall group engagement.
     *
     * @var array<string, int|float>
     */
    public array $summaryMetrics = [];

    /**
     * Snapshot of per-group engagement statistics keyed by group identifier.
     *
     * @var array<int, array<string, int|float>>
     */
    public array $groupActivity = [];

    protected $listeners = ['refresh' => '$refresh'];

    protected $rules = [
        'name' => 'required|string|min:3|max:100',
        'description' => 'required|string|max:500',
        'categoryId' => 'required|exists:group_categories,id',
        'visibility' => 'required|in:open,closed,secret',
        'location' => 'nullable|string|max:100',
        'coverImage' => 'nullable|image|max:1024',
        'icon' => 'nullable|image|max:1024',
    ];

    /**
     * Persist a new group using the management dashboard inputs.
     */
    public function createGroup()
    {
        $this->validate();

        $slug = Group::generateUniqueSlug($this->name);

        $data = [
            'name' => $this->name,
            'slug' => $slug,
            'description' => $this->description,
            'category_id' => $this->categoryId,
            'visibility' => $this->visibility,
            'location' => $this->location,
            'rules' => $this->groupRules,
            'creator_id' => auth()->id(),
        ];
        
        if ($this->coverImage) {
            $data['cover_image'] = $this->coverImage->store('group-covers', 'public');
        }
        
        if ($this->icon) {
            $data['icon'] = $this->icon->store('group-icons', 'public');
        }
        
        $group = Group::create($data);
        
        // Add creator as admin
        // Ensure the creator immediately receives administrative membership status.
        $group->members()->syncWithoutDetaching([
            auth()->id() => [
                'role' => 'admin',
                'status' => 'active',
                'joined_at' => now(),
            ],
        ]);

        $this->resetForm();
        $this->showCreateModal = false;

        return redirect()->route('group.detail', $group);
    }

    /**
     * Reset the modal form back to its default state.
     */
    public function resetForm()
    {
        $this->name = '';
        $this->description = '';
        $this->categoryId = '';
        $this->visibility = 'open';
        $this->groupRules = [];
        $this->location = '';
        $this->coverImage = null;
        $this->icon = null;
    }

    /**
     * Join or request to join the supplied group depending on its visibility.
     */
    public function joinGroup($groupId)
    {
        $group = Group::findOrFail($groupId);

        $userId = auth()->id();

        $existingMembership = $group->members()->where('users.id', $userId)->first();

        if ($existingMembership && $existingMembership->pivot->status === 'active') {
            session()->flash('message', 'You are already an active member of this group.');

            return;
        }

        if ($group->isOpen()) {
            // Direct joins activate the membership and capture the join timestamp.
            $group->members()->syncWithoutDetaching([
                $userId => [
                    'role' => 'member',
                    'status' => 'active',
                    'joined_at' => now(),
                ],
            ]);
            $group->clearUserCache(auth()->user());
            session()->flash('message', 'You have joined the group successfully!');

            return;
        }

        // Closed and secret groups capture intent while awaiting moderator approval.
        $group->members()->syncWithoutDetaching([
            $userId => [
                'role' => 'member',
                'status' => 'pending',
                'joined_at' => null,
            ],
        ]);
        $group->clearUserCache(auth()->user());
        session()->flash('message', 'Your request to join has been sent to the group administrators.');
    }

    /**
     * Leave the provided group and flush related caches.
     */
    public function leaveGroup($groupId)
    {
        $group = Group::findOrFail($groupId);
        $detached = $group->members()->detach(auth()->id());

        if ($detached > 0) {
            // Clearing caches ensures permission checks reflect the new membership state.
            $group->clearUserCache(auth()->user());
            session()->flash('message', 'You have left the group.');

            return;
        }

        session()->flash('message', 'You are not currently a member of this group.');
    }

    /**
     * Render the management dashboard with filters and pagination.
     */
    public function render()
    {
        $query = Group::query();

        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        switch ($this->filter) {
            case 'my':
                $query->whereHas('members', function($q) {
                    $q->where('user_id', auth()->id());
                });
                break;
            case 'open':
                $query->where('visibility', 'open');
                break;
            case 'closed':
                $query->where('visibility', 'closed');
                break;
            case 'secret':
                $query->where('visibility', 'secret');
                break;
        }

        // Clone the filtered query before pagination so summary metrics consider the full result set.
        $filteredQuery = clone $query;

        // Build the summary statistics for the currently filtered dataset.
        $this->summaryMetrics = $this->buildSummaryMetrics($filteredQuery);

        /** @var LengthAwarePaginator $groups */
        $groups = $query
            ->with('category')
            ->withCount('members')
            ->latest()
            ->paginate(10);

        // Hydrate per-group activity analytics for the paginated items.
        $this->groupActivity = $this->buildGroupActivity(collect($groups->items()));

        return view('livewire.group.management.index', [
            'groups' => $groups,
            'categories' => Category::getActiveCategories(),
            'summaryMetrics' => $this->summaryMetrics,
            'groupActivity' => $this->groupActivity,
        ])->layout('layouts.app');
    }

    /**
     * Aggregate high level metrics describing group health and engagement.
     */
    protected function buildSummaryMetrics(Builder $query): array
    {
        $totals = $this->defaultSummaryMetrics();

        $totalGroups = (clone $query)->count();
        $groupIds = (clone $query)->pluck('groups.id')->all();

        if ($totalGroups === 0 || empty($groupIds)) {
            $totals['total_groups'] = 0;

            return $totals;
        }

        $now = Carbon::now();
        $sevenDaysAgo = $now->copy()->subDays(6)->startOfDay();

        $activeMembers = DB::table('group_members')
            ->whereIn('group_id', $groupIds)
            ->where('status', 'active')
            ->count();

        $pendingMembers = DB::table('group_members')
            ->whereIn('group_id', $groupIds)
            ->where('status', 'pending')
            ->count();

        $topicsLastSevenDays = DB::table('group_topics')
            ->whereIn('group_id', $groupIds)
            ->where('created_at', '>=', $sevenDaysAgo)
            ->count();

        $repliesLastSevenDays = DB::table('group_topic_replies')
            ->join('group_topics', 'group_topic_replies.group_topic_id', '=', 'group_topics.id')
            ->whereIn('group_topics.group_id', $groupIds)
            ->where('group_topic_replies.created_at', '>=', $sevenDaysAgo)
            ->count();

        $upcomingEvents = DB::table('group_events')
            ->whereIn('group_id', $groupIds)
            ->where('start_date', '>=', $now)
            ->count();

        $engagementRate = $activeMembers > 0
            ? round(($topicsLastSevenDays + $repliesLastSevenDays) / $activeMembers, 2)
            : 0.0;

        return [
            'total_groups' => $totalGroups,
            'active_members' => $activeMembers,
            'pending_members' => $pendingMembers,
            'topics_last_seven_days' => $topicsLastSevenDays,
            'replies_last_seven_days' => $repliesLastSevenDays,
            'upcoming_events' => $upcomingEvents,
            'engagement_rate' => $engagementRate,
        ];
    }

    /**
     * Compile per-group participation metrics for the supplied collection of groups.
     */
    protected function buildGroupActivity(Collection $groups): array
    {
        if ($groups->isEmpty()) {
            return [];
        }

        $groupIds = $groups->pluck('id')->all();
        $now = Carbon::now();
        $sevenDaysAgo = $now->copy()->subDays(6)->startOfDay();

        $activeMembers = DB::table('group_members')
            ->select('group_id', DB::raw('COUNT(*) as total'))
            ->whereIn('group_id', $groupIds)
            ->where('status', 'active')
            ->groupBy('group_id')
            ->pluck('total', 'group_id');

        $newMembers = DB::table('group_members')
            ->select('group_id', DB::raw('COUNT(*) as total'))
            ->whereIn('group_id', $groupIds)
            ->where('status', 'active')
            ->whereNotNull('joined_at')
            ->where('joined_at', '>=', $sevenDaysAgo)
            ->groupBy('group_id')
            ->pluck('total', 'group_id');

        $topics = DB::table('group_topics')
            ->select('group_id', DB::raw('COUNT(*) as total'))
            ->whereIn('group_id', $groupIds)
            ->where('created_at', '>=', $sevenDaysAgo)
            ->groupBy('group_id')
            ->pluck('total', 'group_id');

        $replies = DB::table('group_topic_replies')
            ->join('group_topics', 'group_topic_replies.group_topic_id', '=', 'group_topics.id')
            ->select('group_topics.group_id as group_id', DB::raw('COUNT(*) as total'))
            ->whereIn('group_topics.group_id', $groupIds)
            ->where('group_topic_replies.created_at', '>=', $sevenDaysAgo)
            ->groupBy('group_topics.group_id')
            ->pluck('total', 'group_topics.group_id');

        $events = DB::table('group_events')
            ->select('group_id', DB::raw('COUNT(*) as total'))
            ->whereIn('group_id', $groupIds)
            ->where('start_date', '>=', $now)
            ->groupBy('group_id')
            ->pluck('total', 'group_id');

        $activity = [];

        foreach ($groups as $group) {
            $groupId = $group->id;
            $activeCount = (int) ($activeMembers[$groupId] ?? 0);
            $topicCount = (int) ($topics[$groupId] ?? 0);
            $replyCount = (int) ($replies[$groupId] ?? 0);

            $activity[$groupId] = [
                'active_members' => $activeCount,
                'new_members' => (int) ($newMembers[$groupId] ?? 0),
                'topics_last_seven_days' => $topicCount,
                'replies_last_seven_days' => $replyCount,
                'upcoming_events' => (int) ($events[$groupId] ?? 0),
                'engagement_rate' => $activeCount > 0
                    ? round(($topicCount + $replyCount) / $activeCount, 2)
                    : 0.0,
            ];
        }

        return $activity;
    }

    /**
     * Provide default zeroed metrics for scenarios with no matching groups.
     */
    protected function defaultSummaryMetrics(): array
    {
        return [
            'total_groups' => 0,
            'active_members' => 0,
            'pending_members' => 0,
            'topics_last_seven_days' => 0,
            'replies_last_seven_days' => 0,
            'upcoming_events' => 0,
            'engagement_rate' => 0.0,
        ];
    }
}
