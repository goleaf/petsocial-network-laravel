<?php

namespace App\Http\Livewire\Group\Management;

use App\Models\Group\Category;
use App\Models\Group\Group;
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

        // Assign the creator the administrator blueprint so permission bridges stay synchronized.
        if (auth()->user()) {
            $group->syncMemberRole(auth()->user(), Group::ROLE_ADMIN, [
                'status' => 'active',
                'joined_at' => now(),
            ]);
        }

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

        $currentUser = auth()->user();
        $userId = $currentUser?->id;

        if (!$currentUser || !$userId) {
            session()->flash('message', 'You must be signed in to manage memberships.');

            return;
        }

        $existingMembership = $group->members()->where('users.id', $userId)->first();

        if ($existingMembership && $existingMembership->pivot->status === 'active') {
            session()->flash('message', 'You are already an active member of this group.');

            return;
        }

        if ($group->isOpen()) {
            // Direct joins activate the membership and capture the join timestamp.
            $group->syncMemberRole($currentUser, Group::ROLE_MEMBER, [
                'status' => 'active',
                'joined_at' => now(),
            ]);
            session()->flash('message', 'You have joined the group successfully!');

            return;
        }

        // Closed and secret groups capture intent while awaiting moderator approval.
        $group->syncMemberRole($currentUser, Group::ROLE_MEMBER, [
            'status' => 'pending',
            'joined_at' => null,
        ]);
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

        $groups = $query->withCount('members')->latest()->paginate(10);

        return view('livewire.group.management.index', [
            'groups' => $groups,
            'categories' => Category::getActiveCategories(),
        ])->layout('layouts.app');
    }
}
