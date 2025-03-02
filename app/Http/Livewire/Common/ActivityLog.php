<?php

namespace App\Http\Livewire\Common;

use App\Traits\EntityTypeTrait;
use App\Traits\FriendshipTrait;
use App\Traits\ActivityTrait;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class ActivityLog extends Component
{
    use EntityTypeTrait, FriendshipTrait, ActivityTrait, WithPagination;
    
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
     * Whether to show the filter controls
     *
     * @var bool
     */
    public $showFilters = true;
    
    /**
     * Whether to show the export button
     *
     * @var bool
     */
    public $showExport = true;
    
    /**
     * Whether to show friend activities
     *
     * @var bool
     */
    public $includeFriendActivities = false;
    
    /**
     * Initialize the component
     *
     * @param string $entityType
     * @param int $entityId
     * @param string $filter
     * @param bool $showFilters
     * @param bool $showExport
     * @param bool $includeFriendActivities
     * @param int $perPage
     * @return void
     */
    public function mount(
        string $entityType,
        int $entityId,
        string $filter = 'all',
        bool $showFilters = true,
        bool $showExport = true,
        bool $includeFriendActivities = false,
        int $perPage = 10
    ) {
        $this->initializeEntity($entityType, $entityId);
        $this->filter = $filter;
        $this->showFilters = $showFilters;
        $this->showExport = $showExport;
        $this->includeFriendActivities = $includeFriendActivities;
        $this->perPage = $perPage;
        
        // Check authorization
        if (!$this->isAuthorized()) {
            abort(403, 'You do not have permission to view this activity log.');
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
     * Toggle whether to include friend activities
     *
     * @return void
     */
    public function toggleFriendActivities()
    {
        $this->resetPage();
        $this->includeFriendActivities = !$this->includeFriendActivities;
    }
    
    /**
     * Export activity log
     *
     * @param string $format
     * @return void
     */
    public function export(string $format)
    {
        // This would be implemented based on the format (csv, pdf, etc.)
        // For now, we'll just emit an event that can be handled by a parent component
        $this->emit('activityLogExportRequested', [
            'entityType' => $this->entityType,
            'entityId' => $this->entityId,
            'format' => $format,
            'filter' => $this->filter,
            'includeFriendActivities' => $this->includeFriendActivities,
        ]);
        
        $this->dispatchBrowserEvent('activity-log-export-requested', [
            'message' => 'Activity log export in ' . $format . ' format initiated.',
        ]);
    }
    
    /**
     * Get activities based on the current filter
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    protected function getActivities()
    {
        $activityModel = $this->getActivityModel();
        $entityField = $this->entityType === 'pet' ? 'pet_id' : 'user_id';
        
        // Start with base query for the entity's activities
        $query = $activityModel::where($entityField, $this->entityId);
        
        // Include friend activities if requested
        if ($this->includeFriendActivities) {
            $friendIds = $this->getFriendIds();
            if (!empty($friendIds)) {
                $query = $activityModel::where(function($q) use ($entityField, $friendIds) {
                    $q->where($entityField, $this->entityId)
                      ->orWhereIn($entityField, $friendIds);
                });
            }
        }
        
        // Apply filter
        switch ($this->filter) {
            case 'friendship':
                $query->where('type', 'like', '%friendship%');
                break;
            case 'post':
                $query->where('type', 'like', '%post%');
                break;
            case 'comment':
                $query->where('type', 'like', '%comment%');
                break;
            case 'like':
                $query->where('type', 'like', '%like%');
                break;
            case 'poll':
                $query->where('type', 'like', '%poll%');
                break;
            case 'all':
            default:
                // No additional filtering
                break;
        }
        
        return $query->orderBy('created_at', 'desc')->paginate($this->perPage);
    }
    
    /**
     * Format activity data for display
     *
     * @param object $activity
     * @return array
     */
    protected function formatActivity($activity)
    {
        $data = json_decode($activity->data, true) ?? [];
        $entityField = $this->entityType === 'pet' ? 'pet_id' : 'user_id';
        $entityModel = $this->getEntityModel();
        
        // Get entity that performed the activity
        $actor = $entityModel::find($activity->$entityField);
        
        // Format the activity based on its type
        $formattedActivity = [
            'id' => $activity->id,
            'type' => $activity->type,
            'actor' => $actor ? [
                'id' => $actor->id,
                'name' => $actor->name,
                'avatar' => $actor->avatar ?? null,
                'url' => $this->entityType === 'pet' ? route('pets.show', $actor->id) : route('users.show', $actor->id),
            ] : null,
            'created_at' => $activity->created_at,
            'data' => $data,
        ];
        
        // Add additional context based on activity type
        switch ($activity->type) {
            case 'friendship_request':
            case 'friendship_accepted':
            case 'friendship_declined':
                if (isset($data['friend_id'])) {
                    $friend = $entityModel::find($data['friend_id']);
                    if ($friend) {
                        $formattedActivity['target'] = [
                            'id' => $friend->id,
                            'name' => $friend->name,
                            'avatar' => $friend->avatar ?? null,
                            'url' => $this->entityType === 'pet' ? route('pets.show', $friend->id) : route('users.show', $friend->id),
                        ];
                    }
                }
                break;
                
            case 'post_created':
            case 'post_updated':
            case 'post_deleted':
                if (isset($data['post_id'])) {
                    $formattedActivity['post_id'] = $data['post_id'];
                    $formattedActivity['post_url'] = route('posts.show', $data['post_id']);
                }
                break;
                
            case 'comment_created':
            case 'comment_updated':
            case 'comment_deleted':
                if (isset($data['comment_id']) && isset($data['post_id'])) {
                    $formattedActivity['comment_id'] = $data['comment_id'];
                    $formattedActivity['post_id'] = $data['post_id'];
                    $formattedActivity['post_url'] = route('posts.show', $data['post_id']) . '#comment-' . $data['comment_id'];
                }
                break;
                
            case 'like_added':
            case 'like_removed':
                if (isset($data['likeable_type']) && isset($data['likeable_id'])) {
                    $formattedActivity['likeable_type'] = $data['likeable_type'];
                    $formattedActivity['likeable_id'] = $data['likeable_id'];
                    
                    // Generate URL based on likeable type
                    if ($data['likeable_type'] === 'post') {
                        $formattedActivity['likeable_url'] = route('posts.show', $data['likeable_id']);
                    } elseif ($data['likeable_type'] === 'comment' && isset($data['post_id'])) {
                        $formattedActivity['likeable_url'] = route('posts.show', $data['post_id']) . '#comment-' . $data['likeable_id'];
                    }
                }
                break;
                
            case 'poll_created':
            case 'poll_voted':
                if (isset($data['poll_id'])) {
                    $formattedActivity['poll_id'] = $data['poll_id'];
                    $formattedActivity['poll_url'] = route('polls.show', $data['poll_id']);
                    
                    if ($activity->type === 'poll_voted' && isset($data['option_id'])) {
                        $formattedActivity['option_id'] = $data['option_id'];
                    }
                }
                break;
        }
        
        return $formattedActivity;
    }
    
    /**
     * Render the component
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        $activities = $this->getActivities();
        $formattedActivities = $activities->map(function ($activity) {
            return $this->formatActivity($activity);
        });
        
        return view('livewire.common.activity-log', [
            'activities' => $activities,
            'formattedActivities' => $formattedActivities,
            'entityType' => $this->entityType,
            'entityId' => $this->entityId,
        ]);
    }
}
