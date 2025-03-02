<?php

namespace App\Http\Livewire;

use App\Models\Friendship;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class Friends extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedFriends = [];
    public $category;
    public $showCategoryModal = false;
    public $filterCategory = '';
    
    protected $listeners = ['refresh' => '$refresh'];

    public function removeFriend($userId)
    {
        $friendship = Friendship::where(function ($query) use ($userId) {
            $query->where('sender_id', auth()->id())
                ->where('recipient_id', $userId);
        })->orWhere(function ($query) use ($userId) {
            $query->where('recipient_id', auth()->id())
                ->where('sender_id', $userId);
        })->where('status', 'accepted')->first();
        
        if ($friendship) {
            $friendship->delete();
            $this->emit('refresh');
            session()->flash('message', 'Friend removed successfully.');
        }
    }

    public function bulkRemove()
    {
        if (empty($this->selectedFriends)) {
            session()->flash('error', 'No friends selected.');
            return;
        }
        
        Friendship::where(function ($query) {
            $query->where('sender_id', auth()->id())
                ->whereIn('recipient_id', $this->selectedFriends);
        })->orWhere(function ($query) {
            $query->where('recipient_id', auth()->id())
                ->whereIn('sender_id', $this->selectedFriends);
        })->where('status', 'accepted')->delete();

        $this->selectedFriends = [];
        $this->emit('refresh');
        session()->flash('message', 'Selected friends removed successfully.');
    }

    public function categorizeFriends()
    {
        $this->validate([
            'category' => 'required|string|max:50',
        ]);
        
        if (empty($this->selectedFriends)) {
            session()->flash('error', 'No friends selected.');
            return;
        }

        Friendship::where(function ($query) {
            $query->where('sender_id', auth()->id())
                ->whereIn('recipient_id', $this->selectedFriends);
        })->orWhere(function ($query) {
            $query->where('recipient_id', auth()->id())
                ->whereIn('sender_id', $this->selectedFriends);
        })->where('status', 'accepted')->update(['category' => $this->category]);

        $this->showCategoryModal = false;
        $this->category = null;
        $this->selectedFriends = [];
        $this->emit('refresh');
        session()->flash('message', 'Friends categorized successfully.');
    }
    
    public function setFilterCategory($category = '')
    {
        $this->filterCategory = $category;
        $this->resetPage();
    }
    
    /**
     * Get friend recommendations based on mutual connections
     */
    public function getFriendRecommendations($limit = 5)
    {
        $currentUser = auth()->user();
        $myFriendIds = $currentUser->friends()->pluck('id')->toArray();
        
        // Get all users who are not already friends with the current user
        $potentialFriends = User::where('id', '!=', $currentUser->id)
            ->whereNotIn('id', $myFriendIds)
            ->get();
        
        // Calculate mutual friend count for each potential friend
        $recommendations = [];
        foreach ($potentialFriends as $user) {
            $userFriendIds = $user->friends()->pluck('id')->toArray();
            $mutualFriendCount = count(array_intersect($myFriendIds, $userFriendIds));
            
            if ($mutualFriendCount > 0) {
                $recommendations[] = [
                    'user' => $user,
                    'mutual_count' => $mutualFriendCount
                ];
            }
        }
        
        // Sort by number of mutual friends (descending)
        usort($recommendations, function($a, $b) {
            return $b['mutual_count'] <=> $a['mutual_count'];
        });
        
        // Return the top recommendations
        return array_slice($recommendations, 0, $limit);
    }

    public function render()
    {
        $query = auth()->user()->friends();
        
        if ($this->search) {
            $query->where('name', 'like', "%{$this->search}%");
        }
        
        if ($this->filterCategory) {
            $query->whereHas('friendships', function ($q) {
                $q->where('category', $this->filterCategory);
            });
        }
        
        $friends = $query->paginate(10);
        
        // Get all unique categories for the filter dropdown
        $categories = Friendship::where(function ($query) {
            $query->where('sender_id', auth()->id())
                ->orWhere('recipient_id', auth()->id());
        })->where('status', 'accepted')
          ->whereNotNull('category')
          ->distinct()
          ->pluck('category');
        
        // Get friend recommendations
        $recommendations = $this->getFriendRecommendations();

        return view('livewire.friends', [
            'friends' => $friends,
            'categories' => $categories,
            'recommendations' => $recommendations,
        ])->layout('layouts.app');
    }
}
