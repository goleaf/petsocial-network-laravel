<?php

namespace App\Http\Livewire\Admin;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Livewire\Component;

class ManageUsers extends Component
{
    /**
     * Collection of managed users with eager loaded activity counts.
     *
     * @var \Illuminate\Support\Collection
     */
    public $users;

    /**
     * Reported posts requiring moderation review.
     *
     * @var \Illuminate\Support\Collection
     */
    public $reportedPosts;

    /**
     * Reported comments requiring moderation review.
     *
     * @var \Illuminate\Support\Collection
     */
    public $reportedComments;

    /**
     * Identifier for the user currently being edited.
     *
     * @var int|null
     */
    public $editingUserId;

    /**
     * Temporary name field bound to the edit form.
     *
     * @var string|null
     */
    public $editName;

    /**
     * Temporary email field bound to the edit form.
     *
     * @var string|null
     */
    public $editEmail;

    /**
     * Temporary role selection bound to the edit form.
     *
     * @var string|null
     */
    public $editRole;

    /**
     * Valid role identifiers retrieved from the RBAC configuration.
     *
     * @var array<int, string>
     */
    public $availableRoles = [];

    /**
     * Human-readable labels keyed by role identifier for UI rendering.
     *
     * @var array<string, string>
     */
    public $roleOptions = [];

    /**
     * Suspension modal state is tracked separately so administrators can
     * confidently review the decision before confirming any action.
     *
     * @var int|null
     */
    public $suspendUserId;

    /**
     * Suspension duration entered by the administrator.
     *
     * @var int|null
     */
    public $suspendDays;

    /**
     * Reason recorded alongside the suspension decision.
     *
     * @var string|null
     */
    public $suspendReason;

    public function mount()
    {
        $this->availableRoles = User::availableRoles();
        $this->roleOptions = User::roleOptions();
        $this->loadData();
    }

    public function loadData()
    {
        $this->users = User::where('id', '!=', auth()->id())->withCount('activityLogs')->get();
        $this->reportedPosts = Post::whereHas('reports')->with('reports')->get();
        $this->reportedComments = Comment::whereHas('reports')->with('reports')->get();
    }

    public function deleteUser($userId)
    {
        User::find($userId)->delete();
        $this->loadData();
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
            // Use the dedicated helper to ensure suspension logic remains
            // consistent with automated moderation flows.
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
            // Clear the suspension with the central helper to keep audit logs
            // and lifecycle hooks synchronized.
            $user->unsuspend();
            $this->loadData();
        }
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
        }
    }

    public function cancelEdit()
    {
        $this->editingUserId = null;
    }

    public function deletePost($postId)
    {
        Post::find($postId)->delete();
        $this->loadData();
    }

    public function deleteComment($commentId)
    {
        Comment::find($commentId)->delete();
        $this->loadData();
    }

    public function render()
    {
        return view('livewire.admin.manage-users', [
            'roleOptions' => $this->roleOptions,
        ])->layout('layouts.app');
    }
}
