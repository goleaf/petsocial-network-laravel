<?php

namespace App\Http\Livewire\Group\Details;

use App\Models\Group\Group;
use App\Models\User;
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
    
    public function mount(Group $group)
    {
        $this->group = $group;
        $this->loadGroupData();
        
        // Guard secret groups so only active members or invitees with access can view the page payload.
        if ($this->group->visibility === Group::VISIBILITY_SECRET && ! $this->group->members->contains(auth()->id())) {
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
        // Validate input using the canonical visibility constants so "secret" groups stay aligned with other entry points.
        $this->validate([
            'name' => 'required|string|min:3|max:100',
            'description' => 'required|string|max:500',
            'category' => 'required|string',
            'visibility' => 'required|in:' . implode(',', [
                Group::VISIBILITY_OPEN,
                Group::VISIBILITY_CLOSED,
                Group::VISIBILITY_SECRET,
            ]),
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
            'memberRole' => 'required|in:' . implode(',', [
                Group::ROLE_MEMBER,
                Group::ROLE_MODERATOR,
                Group::ROLE_ADMIN,
            ]),
        ]);
        
        $members = User::query()->whereIn('id', $this->selectedMembers)->get();

        foreach ($members as $member) {
            // Delegate to the group helper so custom permission bridges are refreshed automatically.
            $this->group->syncMemberRole($member, $this->memberRole);
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
    
    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
    }
    
    public function render()
    {
        return view('livewire.group.details.show')->layout('layouts.app');
    }
}
