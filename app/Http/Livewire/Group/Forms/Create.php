<?php

namespace App\Http\Livewire\Group\Forms;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Group\Group;
use App\Models\Group\Category;
use Illuminate\Support\Str;

class Create extends Component
{
    use WithFileUploads;

    public $name;
    public $description;
    public $visibility = 'open';
    public $coverImage;
    public $icon;
    public $categoryId;
    public $location;

    protected $rules = [
        'name' => 'required|min:3|max:255',
        'description' => 'required|min:10|max:1000',
        'visibility' => 'required|in:open,closed,secret',
        'coverImage' => 'nullable|image|max:2048',
        'icon' => 'nullable|image|max:1024',
        'categoryId' => 'required|exists:group_categories,id', // Table name remains the same for backward compatibility
        'location' => 'nullable|string|max:255'
    ];

    public function render()
    {
        return view('livewire.group.forms.create', [
            'categories' => Category::all()
        ]);
    }

    public function createGroup()
    {
        $this->validate();

        $group = Group::create([
            'name' => $this->name,
            'slug' => Str::slug($this->name),
            'description' => $this->description,
            'visibility' => $this->visibility,
            'category_id' => $this->categoryId,
            'location' => $this->location,
            'user_id' => auth()->id()
        ]);

        if ($this->coverImage) {
            $group->update([
                'cover_image' => $this->coverImage->store('groups/cover-images', 'public')
            ]);
        }

        if ($this->icon) {
            $group->update([
                'icon' => $this->icon->store('groups/icons', 'public')
            ]);
        }

        // Add creator as admin
        $group->members()->attach(auth()->id(), ['role' => 'admin']);

        session()->flash('message', 'Group created successfully!');
        $this->redirect(route('group.detail', $group));
    }
}
