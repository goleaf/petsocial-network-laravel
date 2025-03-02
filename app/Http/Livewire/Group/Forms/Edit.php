<?php

namespace App\Http\Livewire\Group\Forms;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Group\Group;
use App\Models\Group\Category;
use Illuminate\Support\Str;

class Edit extends Component
{
    use WithFileUploads;

    public Group $group;
    public $name;
    public $description;
    public $visibility;
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

    public function mount(Group $group)
    {
        $this->group = $group;
        $this->name = $group->name;
        $this->description = $group->description;
        $this->visibility = $group->visibility;
        $this->categoryId = $group->category_id;
        $this->location = $group->location;
    }

    public function render()
    {
        return view('livewire.group.forms.edit', [
            'categories' => Category::all()
        ]);
    }

    public function updateGroup()
    {
        $this->validate();

        $this->group->update([
            'name' => $this->name,
            'slug' => Str::slug($this->name),
            'description' => $this->description,
            'visibility' => $this->visibility,
            'category_id' => $this->categoryId,
            'location' => $this->location
        ]);

        if ($this->coverImage) {
            $this->group->update([
                'cover_image' => $this->coverImage->store('groups/cover-images', 'public')
            ]);
        }

        if ($this->icon) {
            $this->group->update([
                'icon' => $this->icon->store('groups/icons', 'public')
            ]);
        }

        session()->flash('message', 'Group updated successfully!');
        $this->redirect(route('group.detail', $this->group));
    }
}
