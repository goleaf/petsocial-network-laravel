<?php

namespace App\Http\Livewire;

use App\Models\Group;
use Livewire\Component;
use Livewire\WithFileUploads;

class GroupDetail extends Component
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
    public $searchMembers = '';
    
    // Invite form
    public $inviteEmail;
    public $inviteMessage;
    
    protected $listeners = [
        'refresh' => '$refresh',
        'topicCreated' => 'handleTopicCreated',
        'eventCreated' => 'handleEventCreated',
    ];
    
    public function mount(Group $group)
    {
        $this->group = $group;
        $this->loadGroupData();
    }
    
    public function loadGroupData()
    {
        $this->name = $this->group->name;
        $this->description = $this->group->description;
        $this->category = $this->group->category;
        $this->visibility = $this->group->visibility;
        $this->location = $this->group->location;
        $this->rules = $this->group->rules ?? [];
    }
    
    public function update()
    {
        $this->validate([
            'name' => 'required|string|min:3|max:100',
            'description' => 'required|string|max:1000',
            'category' => 'required|string|max:50',
            'visibility' => 'required|in:open,closed,secret',
            'location' => 'nullable|string|max:100',
            'newCoverImage' => 'nullable|image|max:2048',
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
        
        session()->flash('message', 'Group updated successfully!');
    }
    
    public function addRule()
    {
        $this->rules[] = '';
    }
    
    public function removeRule($index)
    {
        unset($this->rules[$index]);
        $this->rules = array_values($this->rules);
    }
    
    public function join()
    {
        if ($this->group->isOpen()) {
            // For open groups, join directly
            $this->group->members()->attach(auth()->id(), [
                'role' => 'member',
                'status' => 'active',
                'joined_at' => now(),
            ]);
            
            session()->flash('message', 'You have joined the group!');
        } else {
            // For closed groups, send a join request
            $this->group->members()->attach(auth()->id(), [
                'role' => 'member',
                'status' => 'pending',
            ]);
            
            session()->flash('message', 'Your request to join has been sent to the group admins.');
        }
        
        $this->emit('refresh');
    }
    
    public function leave()
    {
        $this->group->members()->detach(auth()->id());
        session()->flash('message', 'You have left the group.');
        $this->emit('refresh');
    }
    
    public function updateMemberRole()
    {
        $this->validate([
            'memberRole' => 'required|in:member,moderator,admin',
        ]);
        
        foreach ($this->selectedMembers as $memberId) {
            $this->group->members()->updateExistingPivot($memberId, [
                'role' => $this->memberRole,
            ]);
        }
        
        $this->reset(['selectedMembers', 'memberRole']);
        session()->flash('message', 'Member roles updated successfully.');
    }
    
    public function removeMember($memberId)
    {
        $this->group->members()->detach($memberId);
        session()->flash('message', 'Member removed from the group.');
        $this->emit('refresh');
    }
    
    public function banMember($memberId)
    {
        $this->group->members()->updateExistingPivot($memberId, [
            'status' => 'banned',
        ]);
        
        session()->flash('message', 'Member has been banned from the group.');
        $this->emit('refresh');
    }
    
    public function approveRequest($memberId)
    {
        $this->group->members()->updateExistingPivot($memberId, [
            'status' => 'active',
            'joined_at' => now(),
        ]);
        
        session()->flash('message', 'Member request approved.');
        $this->emit('refresh');
    }
    
    public function rejectRequest($memberId)
    {
        $this->group->members()->detach($memberId);
        
        session()->flash('message', 'Member request rejected.');
        $this->emit('refresh');
    }
    
    public function sendInvite()
    {
        $this->validate([
            'inviteEmail' => 'required|email',
            'inviteMessage' => 'nullable|string|max:500',
        ]);
        
        // In a real implementation, you would check if the email exists in your users table
        // and send an invitation notification or email
        
        session()->flash('message', 'Invitation sent successfully.');
        $this->reset(['inviteEmail', 'inviteMessage']);
        $this->showInviteModal = false;
    }
    
    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
    }
    
    public function handleTopicCreated()
    {
        $this->activeTab = 'topics';
        $this->emit('refresh');
    }
    
    public function handleEventCreated()
    {
        $this->activeTab = 'events';
        $this->emit('refresh');
    }
    
    public function render()
    {
        $members = $this->group->members()
            ->when($this->searchMembers, function ($query) {
                $query->where('name', 'like', "%{$this->searchMembers}%");
            })
            ->withPivot('role', 'status', 'joined_at')
            ->paginate(10);
            
        $pendingRequests = $this->group->members()
            ->wherePivot('status', 'pending')
            ->withPivot('created_at')
            ->get();
            
        $isAdmin = $this->group->isAdmin(auth()->user());
        $isModerator = $this->group->isModerator(auth()->user());
        $isMember = $this->group->isMember(auth()->user());
        $isPending = $this->group->isPendingMember(auth()->user());
        
        return view('livewire.group-detail', [
            'members' => $members,
            'pendingRequests' => $pendingRequests,
            'isAdmin' => $isAdmin,
            'isModerator' => $isModerator,
            'isMember' => $isMember,
            'isPending' => $isPending,
            'canModerate' => $isAdmin || $isModerator,
        ])->layout('layouts.app');
    }
}
