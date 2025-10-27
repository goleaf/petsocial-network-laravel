<?php

namespace App\Http\Livewire\Admin;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Livewire\Component;

class ManageUsers extends Component
{
    public $users;

    public $reportedPosts;

    public $reportedComments;

    public $editingUserId;

    public $editName;

    public $editEmail;

    public $editRole;

    // Suspension modal state is tracked separately so administrators can
    // confidently review the decision before confirming any action.
    public $suspendUserId;

    public $suspendDays;

    public $suspendReason;

    public function mount()
    {
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
            'editRole' => 'required|in:user,admin',
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
        return view('livewire.admin.manage-users')->layout('layouts.app');
    }
}
