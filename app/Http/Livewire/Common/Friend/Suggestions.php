<?php

namespace App\Http\Livewire\Common\Friend;

use App\Models\User;
use App\Models\Pet;
use App\Traits\EntityTypeTrait;
use App\Traits\FriendshipTrait;
use Livewire\Component;
use Illuminate\Support\Facades\Cache;

class Suggestions extends Component
{
    use EntityTypeTrait, FriendshipTrait;
    
    public $suggestions;
    public $limit = 5;

    public function mount($entityType = 'user', $entityId = null)
    {
        $this->entityType = $entityType;
        $this->entityId = $entityId ?? ($entityType === 'user' ? auth()->id() : null);
        
        if (!$this->entityId) {
            throw new \InvalidArgumentException(__('friends.entity_id_required'));
        }
        
        $this->loadSuggestions();
    }

    public function loadSuggestions()
    {
        $entity = $this->getEntity();
        $cacheKey = "{$this->entityType}_{$this->entityId}_suggestions";
        
        $this->suggestions = Cache::remember($cacheKey, now()->addHours(1), function () use ($entity) {
            $suggestions = $this->getFriendSuggestions($this->limit);
            
            // Format suggestions for display
            return collect($suggestions)->map(function ($suggestion) {
                return [
                    'entity' => $suggestion['entity'],
                    'score' => $suggestion['score'],
                    'mutual_friends_count' => $suggestion['mutual_friends_count'],
                    'mutual_friends' => $suggestion['mutual_friends']
                ];
            });
        });
    }

    public function render()
    {
        return view('livewire.common.friend.suggestions', [
            'entity' => $this->getEntity()
        ]);
    }
}
