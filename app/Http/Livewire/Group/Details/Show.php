<?php

namespace App\Http\Livewire\Group\Details;

use App\Models\Group\Group;
use App\Models\Group\Resource as GroupResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

class Show extends Component
{
    use WithFileUploads;
    
    public Group $group;
    public $showEditModal = false;
    public $showMembersModal = false;
    public $showInviteModal = false;
    public $activeTab = 'topics';
    
    // Edit form properties
    public $name;
    public $description;
    public $category;
    public $visibility;
    public $location;
    public $newCoverImage;
    public $newIcon;
    public $rules = [];
    
    // Member management
    public $selectedMembers = [];
    public $memberRole;
    public $inviteEmail;
    public $inviteMessage;
    
    // For reporting
    public $reportReason;

    // Resource sharing form state
    public $resourceTitle = '';
    public $resourceDescription = '';
    public $resourceType = 'link';
    public $resourceUrl = '';
    public $resourceDocument;
    
    public function mount(Group $group)
    {
        $this->group = $group;
        $this->loadGroupData();
        
        // Check if user is authorized to view this group
        if ($this->group->visibility === 'private' && !$this->group->members->contains(auth()->id())) {
            abort(403, 'You do not have permission to view this group.');
        }
    }
    
    public function loadGroupData()
    {
        $this->name = $this->group->name;
        $this->description = $this->group->description;
        $this->category = $this->group->category;
        $this->visibility = $this->group->visibility;
        $this->location = $this->group->location;
        $this->rules = $this->group->rules;
    }
    
    public function updateGroup()
    {
        $this->validate([
            'name' => 'required|string|min:3|max:100',
            'description' => 'required|string|max:500',
            'category' => 'required|string',
            'visibility' => 'required|in:open,closed,private',
            'location' => 'nullable|string|max:100',
            'rules' => 'nullable|array',
            'newCoverImage' => 'nullable|image|max:1024',
            'newIcon' => 'nullable|image|max:1024',
        ]);
        
        $data = [
            'name' => $this->name,
            'description' => $this->description,
            'category' => $this->category,
            'visibility' => $this->visibility,
            'location' => $this->location,
            'rules' => $this->rules,
        ];
        
        if ($this->newCoverImage) {
            $data['cover_image'] = $this->newCoverImage->store('group-covers', 'public');
        }
        
        if ($this->newIcon) {
            $data['icon'] = $this->newIcon->store('group-icons', 'public');
        }
        
        $this->group->update($data);
        $this->showEditModal = false;
        $this->emit('groupUpdated');
    }
    
    public function updateMemberRoles()
    {
        $this->validate([
            'selectedMembers' => 'required|array|min:1',
            'memberRole' => 'required|in:member,moderator,admin',
        ]);
        
        foreach ($this->selectedMembers as $memberId) {
            $this->group->members()->updateExistingPivot($memberId, ['role' => $this->memberRole]);
        }
        
        $this->selectedMembers = [];
        $this->showMembersModal = false;
        $this->emit('membersUpdated');
    }
    
    public function removeMember($memberId)
    {
        $this->group->members()->detach($memberId);
        $this->emit('memberRemoved');
    }
    
    public function inviteMember()
    {
        $this->validate([
            'inviteEmail' => 'required|email',
            'inviteMessage' => 'nullable|string|max:200',
        ]);
        
        // Send invitation logic here
        
        $this->inviteEmail = '';
        $this->inviteMessage = '';
        $this->showInviteModal = false;
    }
    
    public function leaveGroup()
    {
        $this->group->members()->detach(auth()->id());
        return redirect()->route('groups');
    }
    
    public function reportGroup()
    {
        $this->validate([
            'reportReason' => 'required|string|min:10|max:500',
        ]);
        
        $this->group->reports()->create([
            'user_id' => auth()->id(),
            'reason' => $this->reportReason,
        ]);
        
        $this->reportReason = '';
        session()->flash('message', 'Group reported successfully.');
    }

    /**
     * Validation rules for the resource sharing workflow.
     */
    protected function resourceRules(): array
    {
        $baseRules = [
            'resourceTitle' => 'required|string|max:150',
            'resourceDescription' => 'nullable|string|max:500',
            'resourceType' => ['required', Rule::in(['link', 'document'])],
        ];

        if ($this->resourceType === 'link') {
            $baseRules['resourceUrl'] = 'required|url|max:500';
        } else {
            // Accept common document formats up to 5MB so teams can share reference files.
            $baseRules['resourceDocument'] = 'required|file|max:5120|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx';
        }

        return $baseRules;
    }

    /**
     * Persist a shared resource for the group.
     */
    public function shareResource(): void
    {
        $user = auth()->user();

        if ($user === null || !$this->group->canShareResources($user)) {
            abort(403, 'You do not have permission to share resources in this group.');
        }

        $validated = $this->validate($this->resourceRules());

        $fileAttributes = [
            'file_path' => null,
            'file_name' => null,
            'file_size' => null,
            'file_mime' => null,
        ];

        if ($this->resourceType === 'document' && $this->resourceDocument !== null) {
            // Store the uploaded document on the public disk for straightforward downloads.
            $storedPath = $this->resourceDocument->store('group-resources', 'public');
            $fileAttributes = [
                'file_path' => $storedPath,
                'file_name' => $this->resourceDocument->getClientOriginalName(),
                'file_size' => $this->resourceDocument->getSize(),
                'file_mime' => $this->resourceDocument->getClientMimeType(),
            ];
        }

        GroupResource::query()->create(array_merge([
            'group_id' => $this->group->id,
            'user_id' => $user->id,
            'title' => $validated['resourceTitle'],
            'description' => $validated['resourceDescription'] ?? null,
            'type' => $validated['resourceType'],
            'url' => $validated['resourceType'] === 'link' ? $validated['resourceUrl'] : null,
        ], $fileAttributes));

        // Refresh the eager loaded counts so the header metric stays in sync.
        $this->group->loadCount(['resources']);
        $this->resetResourceForm();
        session()->flash('message', 'Resource shared with the group.');
    }

    /**
     * Remove a resource that either belongs to the member or a moderator.
     */
    public function deleteResource(int $resourceId): void
    {
        $user = auth()->user();

        if ($user === null) {
            abort(403, 'You must be signed in to manage group resources.');
        }

        $resource = $this->group->resources()->findOrFail($resourceId);

        if ($resource->user_id !== $user->id
            && !$this->group->isModerator($user)
            && !$this->group->isAdmin($user)
            && !$user->isAdmin()) {
            abort(403, 'You cannot delete this resource.');
        }

        if ($resource->file_path !== null) {
            // Proactively delete the stored document before removing the database record.
            Storage::disk('public')->delete($resource->file_path);
        }

        $resource->delete();
        $this->group->loadCount(['resources']);
        session()->flash('message', 'Resource removed.');
    }

    /**
     * Reset the resource form fields after persistence.
     */
    public function resetResourceForm(): void
    {
        $this->resourceTitle = '';
        $this->resourceDescription = '';
        $this->resourceType = 'link';
        $this->resourceUrl = '';
        $this->resourceDocument = null;
    }

    /**
     * Adjust form state when the resource type switches tabs.
     */
    public function updatedResourceType(): void
    {
        if ($this->resourceType === 'link') {
            $this->resourceDocument = null;
        } else {
            $this->resourceUrl = '';
        }
    }

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function render()
    {
        return view('livewire.group.details.show', [
            // Provide the cached resource listing to the Blade view for iteration.
            'resources' => GroupResource::forGroup($this->group->id),
        ])->layout('layouts.app');
    }
}
