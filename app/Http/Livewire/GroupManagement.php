<?php

namespace App\Http\Livewire;

use App\Models\Group;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Illuminate\Support\Str;

class GroupManagement extends Component
{
    use WithFileUploads, WithPagination;

    public $name;
    public $description;
    public $category;
    public $visibility = 'open';
    public $rules = [];
    public $location;
    public $coverImage;
    public $icon;
    
    public $search = '';
    public $filter = 'all';
    public $showCreateModal = false;
    
    protected $listeners = ['refresh' => '$refresh'];
    
    protected $rules = [
        'name' => 'required|string|min:3|max:100',
        'description' => 'required|string|max:1000',
        'category' => 'required|string|max:50',
        'visibility' => 'required|in:open,closed,secret',
        'location' => 'nullable|string|max:100',
        'coverImage' => 'nullable|image|max:2048',
        'icon' => 'nullable|image|max:1024',
    ];
    
    public function create()
    {
        $this->validate();
        
        $data = [
            'name' => $this->name,
            'description' => $this->description,
            'category' => $this->category,
            'visibility' => $this->visibility,
            'creator_id' => auth()->id(),
            'location' => $this->location,
        ];
        
        if ($this->coverImage) {
            $data['cover_image'] = $this->coverImage->store('group-covers', 'public');
        }
        
        if ($this->icon) {
            $data['icon'] = $this->icon->store('group-icons', 'public');
        }
        
        if (!empty($this->rules)) {
            $data['rules'] = $this->rules;
        }
        
        $group = Group::create($data);
        
        // Add creator as admin
        $group->members()->attach(auth()->id(), [
            'role' => 'admin',
            'status' => 'active',
            'joined_at' => now(),
        ]);
        
        $this->reset(['name', 'description', 'category', 'visibility', 'location', 'coverImage', 'icon', 'rules']);
        $this->showCreateModal = false;
        
        session()->flash('message', 'Group created successfully!');
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
    
    public function render()
    {
        $query = Group::query();
        
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('description', 'like', "%{$this->search}%");
            });
        }
        
        switch ($this->filter) {
            case 'my':
                $query->whereHas('members', function ($q) {
                    $q->where('users.id', auth()->id());
                });
                break;
            case 'admin':
                $query->whereHas('members', function ($q) {
                    $q->where('users.id', auth()->id())
                      ->where('role', 'admin');
                });
                break;
            case 'open':
                $query->where('visibility', 'open');
                break;
            case 'closed':
                $query->where('visibility', 'closed');
                break;
            case 'secret':
                $query->where('visibility', 'secret')
                      ->whereHas('members', function ($q) {
                          $q->where('users.id', auth()->id());
                      });
                break;
            default:
                $query->visible(auth()->user());
                break;
        }
        
        $groups = $query->latest()->paginate(12);
        
        return view('livewire.group-management', [
            'groups' => $groups,
        ])->layout('layouts.app');
    }
}
