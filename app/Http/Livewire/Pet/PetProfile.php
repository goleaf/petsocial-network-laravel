<?php

namespace App\Http\Livewire\Pet;

use App\Models\Pet;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class PetProfile extends Component
{
    public $petId;
    public $pet;
    public $showFriends = false;
    public $showPhotos = false;
    public $showActivities = false;
    
    protected $listeners = [
        'refreshPetProfile' => '$refresh'
    ];
    
    /**
     * Initialize the component.
     *
     * @param \App\Models\Pet|int $pet
     * @return void
     */
    public function mount(Pet|int $pet): void
    {
        // Normalise the incoming route parameter so cached lookups always use a scalar identifier.
        $this->petId = $pet instanceof Pet ? $pet->getKey() : $pet;

        $this->loadPet();
    }
    
    /**
     * Load the pet data with eager loading for better performance
     *
     * @return void
     */
    public function loadPet()
    {
        // Cache the pet data for 5 minutes to improve performance
        $cacheKey = "pet_profile_{$this->petId}";
        
        $this->pet = Cache::remember($cacheKey, now()->addMinutes(5), function () {
            return Pet::with(['user'])->findOrFail($this->petId);
        });
        
        // Check if the current user is the owner or if the pet is public
        if ($this->pet->user_id !== auth()->id() && !$this->pet->is_public) {
            abort(403, 'You do not have permission to view this pet profile.');
        }
    }
    
    /**
     * Toggle the friends section
     *
     * @return void
     */
    public function toggleFriends()
    {
        $this->showFriends = !$this->showFriends;
        $this->showPhotos = false;
        $this->showActivities = false;
    }
    
    /**
     * Toggle the photos section
     *
     * @return void
     */
    public function togglePhotos()
    {
        $this->showPhotos = !$this->showPhotos;
        $this->showFriends = false;
        $this->showActivities = false;
    }
    
    /**
     * Toggle the activities section
     *
     * @return void
     */
    public function toggleActivities()
    {
        $this->showActivities = !$this->showActivities;
        $this->showFriends = false;
        $this->showPhotos = false;
    }
    
    /**
     * Render the component
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        // Get friend count with caching
        $friendCount = Cache::remember("pet_{$this->petId}_friend_count", now()->addMinutes(10), function () {
            return count($this->pet->getFriendIds());
        });
        
        // Get recent activities with caching (already implemented in the Pet model)
        $recentActivities = $this->showActivities ? $this->pet->recentActivities(10) : collect();
        
        return view('livewire.pet.profile', [
            'pet' => $this->pet,
            'friendCount' => $friendCount,
            'isOwner' => $this->pet->user_id === auth()->id(),
            'recentActivities' => $recentActivities,
        ])->layout('layouts.app');
    }
}
