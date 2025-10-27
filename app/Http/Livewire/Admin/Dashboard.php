<?php

namespace App\Http\Livewire\Admin;

use App\Models\Comment;
use App\Models\FriendRequest;
use App\Models\Message;
use App\Models\Pet;
use App\Models\Post;
use App\Models\Reaction;
use App\Models\Share;
use App\Models\User;
use App\Models\UserActivity;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class Dashboard extends Component
{
    use WithPagination;

    // General stats
    public $totalUsers;

    public $totalPets;

    public $totalPosts;

    public $totalComments;

    public $totalReactions;

    public $totalShares;

    public $totalFriendships;

    public $totalMessages;

    // Activity stats
    public $newUsersToday;

    public $newPostsToday;

    public $activeUsersToday;

    public $topUsers;

    public $topPets;

    // Content moderation
    public $reportedPosts;

    public $reportedComments;

    public $reportedUsers;

    // User management
    public $recentUsers;

    public $suspendedUsers;

    // Search and filters
    public $searchTerm = '';

    public $dateFilter = 'all';

    public $contentTypeFilter = 'all';

    public $statusFilter = 'all';

    // Modals
    public $showUserModal = false;

    public $showPetModal = false;

    public $showPostModal = false;

    public $showReportModal = false;

    public $selectedUserId;

    public $selectedPetId;

    public $selectedPostId;

    public $selectedReportId;

    // User editing
    public $editingUserId;

    public $editName;

    public $editEmail;

    public $editRole;

    /**
     * Valid role identifiers available for reassignment.
     *
     * @var array<int, string>
     */
    public $availableRoles = [];

    /**
     * Mapped labels for role selection dropdowns.
     *
     * @var array<string, string>
     */
    public $roleOptions = [];

    // Suspension
    public $suspendUserId;

    public $suspendDays;

    public $suspendReason;

    // Active Tab
    public $activeTab = 'overview';

    protected $queryString = ['activeTab'];

    protected $listeners = [
        'refresh' => '$refresh',
        'userUpdated' => 'loadData',
        'postDeleted' => 'loadData',
        'commentDeleted' => 'loadData',
        'changeTab' => 'setActiveTab',
    ];

    public function mount()
    {
        $this->availableRoles = User::availableRoles();
        $this->roleOptions = User::roleOptions();
        $this->loadData();
    }

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function loadData()
    {
        // General stats
        $this->totalUsers = User::count();
        $this->totalPets = Pet::count();
        $this->totalPosts = Post::count();
        $this->totalComments = Comment::count();
        $this->totalReactions = Reaction::count();
        $this->totalShares = Share::count();
        $this->totalFriendships = FriendRequest::where('status', 'accepted')->count() / 2; // Divide by 2 since mutual
        $this->totalMessages = Message::count();

        // Activity stats
        $this->newUsersToday = User::whereDate('created_at', Carbon::today())->count();
        $this->newPostsToday = Post::whereDate('created_at', Carbon::today())->count();
        $this->activeUsersToday = UserActivity::whereDate('created_at', Carbon::today())
            ->distinct('user_id')
            ->count('user_id');

        // Top users and pets
        $this->topUsers = User::withCount(['posts', 'comments', 'reactions'])
            ->orderByRaw('posts_count + comments_count + reactions_count DESC')
            ->limit(5)
            ->get();

        $this->topPets = Pet::withCount(['activities'])
            ->orderBy('activities_count', 'desc')
            ->limit(5)
            ->get();

        // Content moderation
        $this->reportedPosts = Post::whereHas('reports')
            ->with(['user', 'reports.user'])
            ->limit(5)
            ->get();

        $this->reportedComments = Comment::whereHas('reports')
            ->with(['user', 'reports.user'])
            ->limit(5)
            ->get();

        $this->reportedUsers = User::whereHas('reports')
            ->with('reports.user')
            ->limit(5)
            ->get();

        // User management
        $this->recentUsers = User::orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $this->suspendedUsers = User::whereNotNull('suspended_at')
            ->where(function ($query) {
                $query->whereNull('suspension_ends_at')
                    ->orWhere('suspension_ends_at', '>', now());
            })
            ->limit(5)
            ->get();
    }

    public function getActivityStats()
    {
        $thirtyDaysAgo = now()->subDays(30);

        return [
            'users' => User::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
                ->where('created_at', '>=', $thirtyDaysAgo)
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                ->pluck('count', 'date')
                ->toArray(),

            'posts' => Post::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
                ->where('created_at', '>=', $thirtyDaysAgo)
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                ->pluck('count', 'date')
                ->toArray(),

            'activities' => UserActivity::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
                ->where('created_at', '>=', $thirtyDaysAgo)
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                ->pluck('count', 'date')
                ->toArray(),
        ];
    }

    // User management methods
    public function viewUser($userId)
    {
        $this->selectedUserId = $userId;
        $this->showUserModal = true;
    }

    public function viewPet($petId)
    {
        $this->selectedPetId = $petId;
        $this->showPetModal = true;
    }

    public function viewPost($postId)
    {
        $this->selectedPostId = $postId;
        $this->showPostModal = true;
    }

    public function viewReport($reportId)
    {
        $this->selectedReportId = $reportId;
        $this->showReportModal = true;
    }

    public function editUser($userId)
    {
        $user = User::find($userId);
        if ($user) {
            $this->editingUserId = $userId;
            $this->editName = $user->name;
            $this->editEmail = $user->email;
            $this->editRole = $user->role;
        }
    }

    public function updateUser()
    {
        $this->validate([
            'editName' => 'required|string|max:255',
            'editEmail' => 'required|string|email|max:255|unique:users,email,'.$this->editingUserId,
            'editRole' => 'required|in:'.implode(',', $this->availableRoles),
        ]);

        $user = User::find($this->editingUserId);
        if ($user) {
            $user->update([
                'name' => $this->editName,
                'email' => $this->editEmail,
                'role' => $this->editRole,
            ]);
            $this->editingUserId = null;
            $this->loadData();
            $this->emit('userUpdated');
        }
    }

    public function cancelEdit()
    {
        $this->editingUserId = null;
    }

    public function suspendUser($userId)
    {
        $this->suspendUserId = $userId;
    }

    public function confirmSuspend()
    {
        $this->validate([
            'suspendDays' => 'nullable|integer|min:1',
            'suspendReason' => 'required|string|max:255',
        ]);

        $user = User::find($this->suspendUserId);
        if ($user) {
            // Delegate to the helper so automated and manual actions share
            // identical execution paths and audit logging.
            $duration = $this->suspendDays !== null && $this->suspendDays !== ''
                ? (int) $this->suspendDays
                : null;
            $user->suspend($duration, $this->suspendReason, false);
            $this->suspendUserId = null;
            $this->suspendDays = null;
            $this->suspendReason = null;
            $this->loadData();
        }
    }

    public function unsuspendUser($userId)
    {
        $user = User::find($userId);
        if ($user) {
            // Using the helper keeps history entries aligned across manual
            // moderation screens and automated workflows.
            $user->unsuspend();
            $this->loadData();
        }
    }

    public function deleteUser($userId)
    {
        User::find($userId)->delete();
        $this->loadData();
    }

    // Content moderation methods
    public function deletePost($postId)
    {
        Post::find($postId)->delete();
        $this->loadData();
        $this->emit('postDeleted');
    }

    public function deleteComment($commentId)
    {
        Comment::find($commentId)->delete();
        $this->loadData();
        $this->emit('commentDeleted');
    }

    public function approvePost($postId)
    {
        $post = Post::find($postId);
        if ($post) {
            $post->reports()->delete();
            $this->loadData();
        }
    }

    public function approveComment($commentId)
    {
        $comment = Comment::find($commentId);
        if ($comment) {
            $comment->reports()->delete();
            $this->loadData();
        }
    }

    public function render()
    {
        return view('livewire.admin.dashboard', [
            'activityStats' => $this->getActivityStats(),
        ])->layout('layouts.app');
    }
}
