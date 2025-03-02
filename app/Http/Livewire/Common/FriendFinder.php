<?php

namespace App\Http\Livewire\Common;

use App\Traits\EntityTypeTrait;
use App\Traits\FriendshipTrait;
use App\Traits\ActivityTrait;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class FriendFinder extends Component
{
    use EntityTypeTrait, FriendshipTrait, ActivityTrait, WithPagination;
    
    /**
     * The current search query
     *
     * @var string
     */
    public $search = '';
    
    /**
     * The current filter
     *
     * @var string
     */
    public $filter = 'all';
    
    /**
     * The number of items to show per page
     *
     * @var int
     */
    public $perPage = 10;
    
    /**
     * Whether to show the import contacts button
     *
     * @var bool
     */
    public $showImportContacts = true;
    
    /**
     * Whether to show the filter controls
     *
     * @var bool
     */
    public $showFilters = true;
    
    /**
     * Whether to show the search box
     *
     * @var bool
     */
    public $showSearch = true;
    
    /**
     * Whether to show the friend button
     *
     * @var bool
     */
    public $showFriendButton = true;
    
    /**
     * Whether to show suggestions
     *
     * @var bool
     */
    public $showSuggestions = true;
    
    /**
     * Initialize the component
     *
     * @param string $entityType
     * @param int $entityId
     * @param string $filter
     * @param bool $showImportContacts
     * @param bool $showFilters
     * @param bool $showSearch
     * @param bool $showFriendButton
     * @param bool $showSuggestions
     * @param int $perPage
     * @return void
     */
    public function mount(
        string $entityType,
        int $entityId,
        string $filter = 'all',
        bool $showImportContacts = true,
        bool $showFilters = true,
        bool $showSearch = true,
        bool $showFriendButton = true,
        bool $showSuggestions = true,
        int $perPage = 10
    ) {
        $this->initializeEntity($entityType, $entityId);
        $this->filter = $filter;
        $this->showImportContacts = $showImportContacts;
        $this->showFilters = $showFilters;
        $this->showSearch = $showSearch;
        $this->showFriendButton = $showFriendButton;
        $this->showSuggestions = $showSuggestions;
        $this->perPage = $perPage;
        
        // Check authorization
        if (!$this->isAuthorized()) {
            abort(403, 'You do not have permission to access this finder.');
        }
    }
    
    /**
     * Set the filter
     *
     * @param string $filter
     * @return void
     */
    public function setFilter(string $filter)
    {
        $this->resetPage();
        $this->filter = $filter;
    }
    
    /**
     * Update the search query
     *
     * @return void
     */
    public function updatedSearch()
    {
        $this->resetPage();
    }
    
    /**
     * Import contacts from various sources
     *
     * @param string $source
     * @return void
     */
    public function importContacts(string $source)
    {
        // This would be implemented based on the source (email, social media, etc.)
        // For now, we'll just emit an event that can be handled by a parent component
        $this->emit('importContactsRequested', [
            'entityType' => $this->entityType,
            'entityId' => $this->entityId,
            'source' => $source,
        ]);
        
        $this->dispatchBrowserEvent('contacts-import-requested', [
            'message' => 'Contact import from ' . $source . ' initiated.',
        ]);
    }
    
    /**
     * Get search results based on the current filter and search query
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    protected function getSearchResults()
    {
        $entityModel = $this->getEntityModel();
        $query = null;
        $friendIds = $this->getFriendIds();
        
        // Base query
        $query = $entityModel::where('id', '!=', $this->entityId);
        
        // Apply filter
        switch ($this->filter) {
            case 'friends':
                $query->whereIn('id', $friendIds);
                break;
            case 'not_friends':
                $query->whereNotIn('id', $friendIds);
                break;
            case 'suggestions':
                // Get suggestions with mutual friends
                return $this->getSuggestions();
            case 'all':
            default:
                // No additional filtering
                break;
        }
        
        // Apply search if provided
        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%');
                  
                if ($this->entityType === 'pet') {
                    $q->orWhere('type', 'like', '%' . $this->search . '%')
                      ->orWhere('breed', 'like', '%' . $this->search . '%');
                }
            });
        }
        
        return $query->orderBy('name')->paginate($this->perPage);
    }
    
    /**
     * Get friend suggestions with mutual friend information
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    protected function getSuggestions()
    {
        $friendIds = $this->getFriendIds();
        $entityModel = $this->getEntityModel();
        
        // Base query for suggestions
        $query = $entityModel::where('id', '!=', $this->entityId)
            ->whereNotIn('id', $friendIds);
            
        // Apply search if provided
        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%');
                  
                if ($this->entityType === 'pet') {
                    $q->orWhere('type', 'like', '%' . $this->search . '%')
                      ->orWhere('breed', 'like', '%' . $this->search . '%');
                }
            });
        }
        
        // Get paginated results
        $suggestions = $query->orderBy('name')->paginate($this->perPage);
        
        // For each suggestion, calculate mutual friends
        foreach ($suggestions as $suggestion) {
            $mutualFriendIds = $this->getMutualFriendIds($suggestion->id);
            $suggestion->mutual_friend_count = count($mutualFriendIds);
            
            // Get mutual friend details if there are any
            if (!empty($mutualFriendIds)) {
                $suggestion->mutual_friends = $entityModel::whereIn('id', array_slice($mutualFriendIds, 0, 3))->get();
            }
        }
        
        return $suggestions;
    }
    
    /**
     * Render the component
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        $results = $this->getSearchResults();
        
        return view('livewire.common.friend-finder', [
            'results' => $results,
            'entityType' => $this->entityType,
            'entityId' => $this->entityId,
        ]);
    }
}
