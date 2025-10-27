<?php

namespace App\Http\Livewire\Pet;

use App\Models\Pet;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class PetManagement extends Component
{
    use WithFileUploads, WithPagination;

    public $name;
    public $type;
    public $breed;
    public $birthdate;
    public $avatar;
    public $location;
    public $bio;
    public $favorite_food;
    public $favorite_toy;
    
    // For edit mode
    public $editMode = false;
    public $petId;
    public $oldAvatar;
    
    // For filtering and searching
    public $search = '';
    public $filter = '';
    
    // For pagination
    protected $paginationTheme = 'tailwind';

    public function mount()
    {
        $this->resetForm();
    }

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'type' => 'nullable|string|max:255',
            'breed' => 'nullable|string|max:255',
            'birthdate' => 'nullable|date',
            'avatar' => $this->editMode ? 'nullable|image|max:2048' : 'nullable|image|max:2048',
            'location' => 'nullable|string|max:100',
            'bio' => 'nullable|string|max:500',
            'favorite_food' => 'nullable|string|max:100',
            'favorite_toy' => 'nullable|string|max:100',
        ];
    }
    
    public function resetForm()
    {
        $this->reset([
            'name', 'type', 'breed', 'birthdate', 'avatar', 'location', 
            'bio', 'favorite_food', 'favorite_toy', 'editMode', 'petId', 'oldAvatar'
        ]);
        $this->resetErrorBag();
        $this->resetValidation();
    }
    
    /**
     * Save a new pet
     */
    public function save()
    {
        $this->validate();

        $data = [
            'user_id' => auth()->id(),
            'name' => $this->name,
            'type' => $this->type,
            'breed' => $this->breed,
            'birthdate' => $this->birthdate,
            'location' => $this->location,
            'bio' => $this->bio,
            'favorite_food' => $this->favorite_food,
            'favorite_toy' => $this->favorite_toy,
            'is_public' => true, // Default to public
        ];

        if ($this->avatar) {
            $data['avatar'] = $this->avatar->store('pet-avatars', 'public');
        }

        $pet = auth()->user()->pets()->create($data);
        
        // Clear user's pet types cache
        Cache::forget('user_' . auth()->id() . '_pet_types');
        
        $this->resetForm();
        session()->flash('message', 'Pet added successfully!');
        $this->dispatch('pet-saved');
    }

    public function edit($petId)
    {
        $pet = auth()->user()->pets()->findOrFail($petId);
        $this->petId = $pet->id;
        $this->name = $pet->name;
        $this->type = $pet->type;
        $this->breed = $pet->breed;
        $this->birthdate = $pet->birthdate;
        $this->location = $pet->location;
        $this->bio = $pet->bio;
        $this->favorite_food = $pet->favorite_food;
        $this->favorite_toy = $pet->favorite_toy;
        $this->oldAvatar = $pet->avatar;
        $this->editMode = true;
    }
    
    /**
     * Update an existing pet
     */
    public function update()
    {
        $this->validate();
        
        $pet = auth()->user()->pets()->findOrFail($this->petId);
        
        $data = [
            'name' => $this->name,
            'type' => $this->type,
            'breed' => $this->breed,
            'birthdate' => $this->birthdate,
            'location' => $this->location,
            'bio' => $this->bio,
            'favorite_food' => $this->favorite_food,
            'favorite_toy' => $this->favorite_toy,
        ];
        
        if ($this->avatar) {
            // Delete old avatar if exists
            if ($pet->avatar && Storage::disk('public')->exists($pet->avatar)) {
                Storage::disk('public')->delete($pet->avatar);
            }
            $data['avatar'] = $this->avatar->store('pet-avatars', 'public');
        }
        
        $pet->update($data);
        
        // Clear any cached data related to this pet
        $this->clearPetCache($pet->id);
        
        $this->resetForm();
        session()->flash('message', 'Pet updated successfully!');
        $this->dispatch('pet-updated');
    }
    
    public function cancelEdit()
    {
        $this->resetForm();
    }
    
    /**
     * Delete a pet and its related data
     */
    public function delete($petId)
    {
        $pet = auth()->user()->pets()->find($petId);
        if ($pet) {
            // Delete avatar if exists
            if ($pet->avatar && Storage::disk('public')->exists($pet->avatar)) {
                Storage::disk('public')->delete($pet->avatar);
            }
            
            // Delete pet activities images
            foreach ($pet->activities as $activity) {
                if ($activity->image && Storage::disk('public')->exists($activity->image)) {
                    Storage::disk('public')->delete($activity->image);
                }
            }
            
            // Delete pet friendships
            $pet->friends()->detach();
            $pet->friendOf()->detach();
            
            // Delete the pet
            $pet->delete();
            
            // Clear any cached data related to this pet
            $this->clearPetCache($pet->id);
            
            session()->flash('message', 'Pet deleted successfully!');
        }
    }

    /**
     * Render the component
     */
    /**
     * Clear cache related to a specific pet
     */
    private function clearPetCache($petId)
    {
        Cache::forget("pet_{$petId}_friend_ids");
        Cache::forget("pet_{$petId}_recent_activities_5");
        Cache::forget('user_' . auth()->id() . '_pet_types');
    }
    
    /**
     * Render the component
     */
    public function render()
    {
        // Use query builder for better performance
        $query = auth()->user()->pets();
        
        // Apply search filter if provided
        if ($this->search) {
            $query->where(function($q) {
                $searchTerm = '%' . $this->search . '%';
                $q->where('name', 'like', $searchTerm)
                  ->orWhere('type', 'like', $searchTerm)
                  ->orWhere('breed', 'like', $searchTerm);
            });
        }
        
        // Apply type filter if provided
        if ($this->filter) {
            $query->where('type', $this->filter);
        }
        
        // Get paginated results with eager loading for better performance
        $pets = $query->with('user')
                      ->latest()
                      ->paginate(5);
        
        // Cache pet types for 10 minutes to improve performance
        $petTypes = Cache::remember('user_' . auth()->id() . '_pet_types', now()->addMinutes(10), function () {
            return auth()->user()->pets()
                               ->distinct()
                               ->pluck('type')
                               ->filter();
        });
        
        return view('livewire.pet.management', [
            'pets' => $pets,
            'petTypes' => $petTypes,
        ])->layout('layouts.app');
    }
}
