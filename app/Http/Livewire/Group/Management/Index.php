<?php

namespace App\Http\Livewire\Group\Management;

use App\Models\Group\Group;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Illuminate\Support\Str;

class Index extends Component
{
    use WithFileUploads, WithPagination;

    public $name;
    public $description;
    public $category;
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
        'category' => 'required|string',
        'visibility' => 'required|in:open,closed,private',
        'location' => 'nullable|string|max:100',
        'coverImage' => 'nullable|image|max:1024',
        'icon' => 'nullable|image|max:1024',
    ];
    
    public function createGroup()
    {
        $this->validate();
        
        $slug = Str::slug($this->name);
        
        // Check if slug exists, if so, append a random string
        if (Group::where('slug', $slug)->exists()) {
            $slug = $slug . '-' . Str::random(5);
        }
        
        $data = [
            'name' => $this->name,
            'slug' => $slug,
            'description' => $this->description,
            'category' => $this->category,
            'visibility' => $this->visibility,
            'location' => $this->location,
            'rules' => $this->groupRules,
            'created_by' => auth()->id(),
        ];
        
        if ($this->coverImage) {
            $data['cover_image'] = $this->coverImage->store('group-covers', 'public');
        }
        
        if ($this->icon) {
            $data['icon'] = $this->icon->store('group-icons', 'public');
        }
        
        $group = Group::create($data);
        
        // Add creator as admin
        $group->members()->attach(auth()->id(), ['role' => 'admin', 'joined_at' => now()]);
        
        $this->resetForm();
        $this->showCreateModal = false;
        
        return redirect()->route('group.detail', $group);
    }
    
    public function resetForm()
    {
        $this->name = '';
        $this->description = '';
        $this->category = '';
        $this->visibility = 'open';
        $this->groupRules = [];
        $this->location = '';
        $this->coverImage = null;
        $this->icon = null;
    }
    
    public function joinGroup($groupId)
    {
        $group = Group::findOrFail($groupId);
        
        if ($group->visibility === 'open') {
            // Direct join for open groups
            $group->members()->attach(auth()->id(), ['role' => 'member', 'joined_at' => now()]);
            session()->flash('message', 'You have joined the group successfully!');
        } else {
            // Request to join for closed groups
            $group->memberRequests()->attach(auth()->id(), ['requested_at' => now()]);
            session()->flash('message', 'Your request to join has been sent to the group administrators.');
        }
    }
    
    public function leaveGroup($groupId)
    {
        $group = Group::findOrFail($groupId);
        $group->members()->detach(auth()->id());
        session()->flash('message', 'You have left the group.');
    }
    
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
        }
        
        $groups = $query->withCount('members')->latest()->paginate(10);
        
        return view('livewire.group.management.index', [
            'groups' => $groups,
        ])->layout('layouts.app');
    }
}
