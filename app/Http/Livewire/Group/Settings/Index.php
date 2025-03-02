<?php

namespace App\Http\Livewire\Group\Settings;

use Livewire\Component;
use App\Models\Group\Group;
use App\Models\Group\Category;

class Index extends Component
{
    public Group $group;
    public $visibility;
    public $categoryId;

    protected $rules = [
        'visibility' => 'required|in:open,closed,secret',
        'categoryId' => 'required|exists:group_categories,id' // Table name remains the same for backward compatibility
    ];

    public function mount(Group $group)
    {
        $this->group = $group;
        $this->visibility = $group->visibility;
        $this->categoryId = $group->category_id;
    }

    public function render()
    {
        return view('livewire.group.settings.index', [
            'categories' => Category::all()
        ]);
    }

    public function updateSettings()
    {
        $this->validate();

        $this->group->update([
            'visibility' => $this->visibility,
            'category_id' => $this->categoryId
        ]);

        session()->flash('message', 'Group settings updated successfully!');
    }
}
