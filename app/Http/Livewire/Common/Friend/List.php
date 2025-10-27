<?php

namespace App\Http\Livewire\Common\Friend;

use App\Models\User;
use App\Models\Pet;
use App\Models\Friendship;
use App\Models\PetFriendship;
use App\Traits\EntityTypeTrait;
use App\Traits\FriendshipTrait;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Cache;

class FriendList extends Component
{
    use WithPagination, EntityTypeTrait, FriendshipTrait;
    
    public $search = '';
    public $categoryFilter = null;
    public $selectedFriends = [];
    public $selectAll = false;
    public $showCategoryModal = false;
    public $newCategory = '';
    public $perPage = 12;
    // Livewire's pagination helper relies on an exposed page property for stateful interactions.
    public $page = 1;
    
    protected $queryString = [
        'search' => ['except' => ''],
        'categoryFilter' => ['except' => ''],
        'page' => ['except' => 1],
    ];
    
    protected $listeners = [
        'refresh' => '$refresh',
        'friendRemoved' => 'handleFriendRemoved',
        'friendAdded' => 'handleFriendAdded',
        'friendCategorized' => 'handleFriendCategorized',
    ];

    public function mount($entityType = 'user', $entityId = null)
    {
        $this->entityType = $entityType;
        $this->entityId = $entityId ?? ($entityType === 'user' ? auth()->id() : null);

        if (!$this->entityId) {
            throw new \InvalidArgumentException(__('friends.entity_id_required'));
        }

        // Enforce profile privacy before exposing a user friend list to other members.
        if ($this->entityType === 'user' && $this->entityId !== auth()->id()) {
            $targetUser = User::findOrFail($this->entityId);
            $viewer = auth()->user();

            if (! $targetUser->canViewPrivacySection($viewer, 'friends') && ! $viewer->isAdmin()) {
                abort(403, __('profile.friend_list_private'));
            }
        }
    }
    
    public function updatingSearch()
    {
        $this->resetPage();
        $this->selectedFriends = [];
        $this->selectAll = false;
    }
    
    public function updatingCategoryFilter()
    {
        $this->resetPage();
        $this->selectedFriends = [];
        $this->selectAll = false;
    }
    
    public function handleFriendRemoved($friendId)
    {
        $this->clearFriendCache();
    }
    
    public function handleFriendAdded($friendId)
    {
        $this->clearFriendCache();
    }
    
    public function handleFriendCategorized()
    {
        $this->clearFriendCache();
    }
    
    public function clearFriendCache()
    {
        $prefix = $this->entityType === 'pet' ? 'pet_' : 'user_';
        Cache::forget("{$prefix}{$this->entityId}_friends_{$this->search}_{$this->categoryFilter}_page{$this->page}");
        Cache::forget("{$prefix}{$this->entityId}_friend_categories");
        $this->clearEntityCache();
    }
    
    public function toggleSelectAll()
    {
        $this->selectAll = !$this->selectAll;
        
        if ($this->selectAll) {
            $friends = $this->getFriends();
            $this->selectedFriends = $friends->pluck('id')->toArray();
        } else {
            $this->selectedFriends = [];
        }
    }
    
    public function getFriendCategories()
    {
        $prefix = $this->entityType === 'pet' ? 'pet_' : 'user_';
        $cacheKey = "{$prefix}{$this->entityId}_friend_categories";
        
        return Cache::remember($cacheKey, now()->addHours(1), function() {
            $friendshipModel = $this->getFriendshipModel();
            $entityIdField = $this->getEntityIdField();
            
            return $friendshipModel::where($entityIdField, $this->entityId)
                ->whereNotNull('category')
                ->select('category')
                ->distinct()
                ->pluck('category')
                ->toArray();
        });
    }
    
    public function getFriends()
    {
        $prefix = $this->entityType === 'pet' ? 'pet_' : 'user_';
        $cacheKey = "{$prefix}{$this->entityId}_friends_{$this->search}_{$this->categoryFilter}_page{$this->page}";
        
        return Cache::remember($cacheKey, now()->addMinutes(5), function() {
            $entity = $this->getEntity();
            $friendIds = $this->getFriendIds();
            
            if (empty($friendIds)) {
                return collect();
            }
            
            $entityModel = $this->getEntityModel();
            $query = $entityModel::whereIn('id', $friendIds);
            
            if ($this->search) {
                $query->where(function($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                      ->orWhere('username', 'like', "%{$this->search}%");
                });
            }
            
            if ($this->categoryFilter) {
                $friendshipModel = $this->getFriendshipModel();
                $entityIdField = $this->getEntityIdField();
                $friendIdField = $this->getFriendIdField();
                
                $categorizedFriendIds = $friendshipModel::where($entityIdField, $this->entityId)
                    ->where('category', $this->categoryFilter)
                    ->pluck($friendIdField)
                    ->toArray();
                    
                $query->whereIn('id', $categorizedFriendIds);
            }
            
            return $query->paginate($this->perPage);
        });
    }
    
    public function removeFriends()
    {
        if (!$this->isAuthorized()) {
            session()->flash('error', __('friends.not_authorized'));
            return;
        }
        
        if (empty($this->selectedFriends)) {
            session()->flash('error', __('friends.no_friends_selected'));
            return;
        }
        
        foreach ($this->selectedFriends as $friendId) {
            $this->removeFriend($friendId);
        }
        
        $this->selectedFriends = [];
        $this->selectAll = false;
        $this->clearFriendCache();
        
        session()->flash('success', __('friends.friends_removed_success'));
        $this->emit('refresh');
    }
    
    public function showCategoryModal()
    {
        if (empty($this->selectedFriends)) {
            session()->flash('error', __('friends.no_friends_selected'));
            return;
        }
        
        $this->showCategoryModal = true;
    }
    
    public function cancelCategoryModal()
    {
        $this->showCategoryModal = false;
        $this->newCategory = '';
    }
    
    public function applyCategory()
    {
        if (!$this->isAuthorized()) {
            session()->flash('error', __('friends.not_authorized'));
            return;
        }
        
        if (empty($this->selectedFriends)) {
            session()->flash('error', __('friends.no_friends_selected'));
            return;
        }
        
        $this->categorizeFriends($this->selectedFriends, $this->newCategory);
        
        $this->showCategoryModal = false;
        $this->newCategory = '';
        $this->selectedFriends = [];
        $this->selectAll = false;
        $this->clearFriendCache();
        
        session()->flash('success', __('friends.category_applied_success'));
        $this->emit('friendCategorized');
        $this->emit('refresh');
    }

    /**
     * Bridge Livewire v2 style event emission to the v3 dispatch helper for compatibility.
     */
    public function emit(string $event, ...$payload): void
    {
        $this->dispatch($event, ...$payload);
    }

    public function render()
    {
        $friends = $this->getFriends();
        $categories = $this->getFriendCategories();
        
        return view('livewire.common.friend.list', [
            'entity' => $this->getEntity(),
            'friends' => $friends,
            'categories' => $categories
        ]);
    }
}

// Provide a backwards-compatible alias so existing route definitions referencing
// `Common\Friend\List` continue to resolve without triggering reserved keyword errors.
if (! class_exists(__NAMESPACE__.'\\List', false)) {
    class_alias(FriendList::class, __NAMESPACE__.'\\List');
}

// Some historical route definitions resolve the component through the short namespace,
// so register that alias as well to avoid container resolution issues during tests.
if (! class_exists('Common\\Friend\\List', false)) {
    class_alias(FriendList::class, 'Common\\Friend\\List');
}
