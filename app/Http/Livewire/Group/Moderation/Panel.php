<?php

namespace App\Http\Livewire\Group\Moderation;

use App\Models\Group\Group;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Panel extends Component
{
    use WithPagination;

    /**
     * Configure the pagination theme to align with the Tailwind UI kit in use.
     */
    protected string $paginationTheme = 'tailwind';

    public Group $group;

    public string $search = '';

    public string $statusFilter = 'pending';

    protected $listeners = ['memberUpdated' => '$refresh'];

    /**
     * Prime component state and ensure the visitor has moderation rights.
     */
    public function mount(Group $group): void
    {
        $this->group = $group;
        $this->ensureModeratorAccess();
    }

    /**
     * Reset pagination when the search term is updated so results stay in sync.
     */
    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Reset pagination when the filter changes to avoid stale pages.
     */
    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    /**
     * Approve a pending member request and activate their membership.
     */
    public function approveMember(int $userId): void
    {
        $this->ensureModeratorAccess();

        $member = $this->findMember($userId);

        if (!$member || $member->pivot->status !== 'pending') {
            return;
        }

        $this->group->members()->updateExistingPivot($userId, [
            'status' => 'active',
            'joined_at' => now(),
        ]);

        $this->group->clearUserCache($member);

        session()->flash('message', 'Member approved successfully.');

        $this->emitSelf('memberUpdated');
    }

    /**
     * Deny a pending membership request and remove it from the queue.
     */
    public function denyMember(int $userId): void
    {
        $this->ensureModeratorAccess();

        $member = $this->findMember($userId);

        if (!$member || $member->pivot->status !== 'pending') {
            return;
        }

        $this->group->members()->detach($userId);

        $this->group->clearUserCache($member);

        session()->flash('message', 'Membership request denied.');

        $this->emitSelf('memberUpdated');
    }

    /**
     * Ban a member to immediately revoke their access.
     */
    public function banMember(int $userId): void
    {
        $this->ensureModeratorAccess();

        $member = $this->findMember($userId);

        if (!$member || $member->pivot->status === 'banned') {
            return;
        }

        $this->group->members()->updateExistingPivot($userId, [
            'status' => 'banned',
        ]);

        $this->group->clearUserCache($member);

        session()->flash('message', 'Member banned from the group.');

        $this->emitSelf('memberUpdated');
    }

    /**
     * Reinstate a previously banned member.
     */
    public function unbanMember(int $userId): void
    {
        $this->ensureModeratorAccess();

        $member = $this->findMember($userId);

        if (!$member || $member->pivot->status !== 'banned') {
            return;
        }

        $this->group->members()->updateExistingPivot($userId, [
            'status' => 'active',
            'joined_at' => $member->pivot->joined_at ?? now(),
        ]);

        $this->group->clearUserCache($member);

        session()->flash('message', 'Member reinstated successfully.');

        $this->emitSelf('memberUpdated');
    }

    /**
     * Remove a member record regardless of its status.
     */
    public function removeMember(int $userId): void
    {
        $this->ensureModeratorAccess();

        $member = $this->findMember($userId);

        if (!$member) {
            return;
        }

        $this->group->members()->detach($userId);

        $this->group->clearUserCache($member);

        session()->flash('message', 'Member removed from the group.');

        $this->emitSelf('memberUpdated');
    }

    /**
     * Locate a specific member from the pivot relationship.
     */
    protected function findMember(int $userId): ?User
    {
        return $this->group->members()
            ->where('users.id', $userId)
            ->first();
    }

    /**
     * Guard the component so only moderators, group admins, or platform admins can access it.
     */
    protected function ensureModeratorAccess(): void
    {
        $user = Auth::user();

        if (!$user) {
            abort(403, 'Authentication required.');
        }

        if ($user->isAdmin()) {
            return;
        }

        if ($this->group->isAdmin($user) || $this->group->isModerator($user)) {
            return;
        }

        abort(403, 'You do not have permission to manage this group.');
    }

    /**
     * Build the base query for retrieving members scoped to the provided filters.
     */
    protected function memberQuery(): BelongsToMany
    {
        return $this->group->members()
            ->withPivot('role', 'status', 'joined_at')
            ->when($this->search !== '', function (BelongsToMany $query): void {
                $query->where(function ($q): void {
                    $q->where('users.name', 'like', '%' . $this->search . '%')
                        ->orWhere('users.username', 'like', '%' . $this->search . '%')
                        ->orWhere('users.email', 'like', '%' . $this->search . '%');
                });
            });
    }

    /**
     * Apply the active status filter and paginate the results for the table UI.
     */
    protected function scopedMembers()
    {
        $query = $this->memberQuery();

        switch ($this->statusFilter) {
            case 'active':
                $query->wherePivot('status', 'active');
                break;
            case 'banned':
                $query->wherePivot('status', 'banned');
                break;
            case 'pending':
            default:
                $query->wherePivot('status', 'pending');
                break;
        }

        return $query
            ->orderBy('group_members.created_at')
            ->paginate(10, ['users.*']);
    }

    /**
     * Summarise key moderation metrics so the view can surface quick stats.
     */
    protected function memberMetrics(): array
    {
        return [
            'pending' => $this->group->members()->wherePivot('status', 'pending')->count(),
            'active' => $this->group->members()->wherePivot('status', 'active')->count(),
            'banned' => $this->group->members()->wherePivot('status', 'banned')->count(),
        ];
    }

    /**
     * Render the moderation panel using the shared application layout.
     */
    public function render(): View
    {
        return view('livewire.group.moderation.panel', [
            'group' => $this->group,
            'members' => $this->scopedMembers(),
            'metrics' => $this->memberMetrics(),
        ])->layout('layouts.app');
    }
}
