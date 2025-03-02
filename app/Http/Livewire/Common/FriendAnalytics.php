<?php

namespace App\Http\Livewire\Common;

use App\Traits\EntityTypeTrait;
use App\Traits\FriendshipTrait;
use App\Traits\ActivityTrait;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class FriendAnalytics extends Component
{
    use EntityTypeTrait, FriendshipTrait, ActivityTrait;
    
    /**
     * The time period for analytics
     *
     * @var string
     */
    public $timePeriod = 'month';
    
    /**
     * The chart type
     *
     * @var string
     */
    public $chartType = 'bar';
    
    /**
     * Whether to show the filter controls
     *
     * @var bool
     */
    public $showFilters = true;
    
    /**
     * Whether to show the chart type selector
     *
     * @var bool
     */
    public $showChartTypeSelector = true;
    
    /**
     * Whether to show the export button
     *
     * @var bool
     */
    public $showExport = true;
    
    /**
     * Initialize the component
     *
     * @param string $entityType
     * @param int $entityId
     * @param string $timePeriod
     * @param string $chartType
     * @param bool $showFilters
     * @param bool $showChartTypeSelector
     * @param bool $showExport
     * @return void
     */
    public function mount(
        string $entityType,
        int $entityId,
        string $timePeriod = 'month',
        string $chartType = 'bar',
        bool $showFilters = true,
        bool $showChartTypeSelector = true,
        bool $showExport = true
    ) {
        $this->initializeEntity($entityType, $entityId);
        $this->timePeriod = $timePeriod;
        $this->chartType = $chartType;
        $this->showFilters = $showFilters;
        $this->showChartTypeSelector = $showChartTypeSelector;
        $this->showExport = $showExport;
        
        // Check authorization
        if (!$this->isAuthorized()) {
            abort(403, 'You do not have permission to view these analytics.');
        }
    }
    
    /**
     * Set the time period
     *
     * @param string $period
     * @return void
     */
    public function setTimePeriod(string $period)
    {
        $this->timePeriod = $period;
    }
    
    /**
     * Set the chart type
     *
     * @param string $type
     * @return void
     */
    public function setChartType(string $type)
    {
        $this->chartType = $type;
    }
    
    /**
     * Export analytics data
     *
     * @param string $format
     * @return void
     */
    public function export(string $format)
    {
        // This would be implemented based on the format (csv, pdf, etc.)
        // For now, we'll just emit an event that can be handled by a parent component
        $this->emit('analyticsExportRequested', [
            'entityType' => $this->entityType,
            'entityId' => $this->entityId,
            'format' => $format,
            'data' => $this->getAnalyticsData(),
        ]);
        
        $this->dispatchBrowserEvent('analytics-export-requested', [
            'message' => 'Analytics export in ' . $format . ' format initiated.',
        ]);
    }
    
    /**
     * Get analytics data based on the current time period
     *
     * @return array
     */
    protected function getAnalyticsData()
    {
        $prefix = $this->entityType === 'pet' ? 'pet_' : 'user_';
        $cacheKey = "{$prefix}{$this->entityId}_friend_analytics_{$this->timePeriod}";
        
        return Cache::remember($cacheKey, now()->addMinutes(30), function () {
            $friendshipModel = $this->getFriendshipModel();
            $entityIdField = $this->getEntityIdField();
            $friendIdField = $this->getFriendIdField();
            
            // Determine the start date based on the time period
            $startDate = now();
            switch ($this->timePeriod) {
                case 'week':
                    $startDate = $startDate->subWeek();
                    $groupFormat = '%Y-%m-%d';
                    break;
                case 'month':
                    $startDate = $startDate->subMonth();
                    $groupFormat = '%Y-%m-%d';
                    break;
                case 'quarter':
                    $startDate = $startDate->subQuarter();
                    $groupFormat = '%Y-%m-%d';
                    break;
                case 'year':
                    $startDate = $startDate->subYear();
                    $groupFormat = '%Y-%m';
                    break;
                default:
                    $startDate = $startDate->subMonth();
                    $groupFormat = '%Y-%m-%d';
            }
            
            // Get new friendships over time
            $newFriendships = $friendshipModel::where(function($query) use ($entityIdField, $friendIdField) {
                $query->where($entityIdField, $this->entityId)
                      ->orWhere($friendIdField, $this->entityId);
            })
            ->where('created_at', '>=', $startDate)
            ->select(DB::raw("DATE_FORMAT(created_at, '{$groupFormat}') as date"), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('count', 'date')
            ->toArray();
            
            // Get friend activity over time
            $activityModel = $this->getActivityModel();
            $entityField = $this->entityType === 'pet' ? 'pet_id' : 'user_id';
            
            $friendIds = $this->getFriendIds();
            $friendActivity = $activityModel::whereIn($entityField, $friendIds)
                ->where('created_at', '>=', $startDate)
                ->select(DB::raw("DATE_FORMAT(created_at, '{$groupFormat}') as date"), DB::raw('count(*) as count'))
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                ->pluck('count', 'date')
                ->toArray();
            
            // Get activity types distribution
            $activityTypes = $activityModel::whereIn($entityField, $friendIds)
                ->where('created_at', '>=', $startDate)
                ->select('type', DB::raw('count(*) as count'))
                ->groupBy('type')
                ->orderBy('count', 'desc')
                ->get()
                ->pluck('count', 'type')
                ->toArray();
            
            // Get friend categories distribution (if applicable)
            $friendCategories = [];
            if ($this->entityType === 'user') {
                $friendCategories = $friendshipModel::where($entityIdField, $this->entityId)
                    ->whereNotNull('category')
                    ->select('category', DB::raw('count(*) as count'))
                    ->groupBy('category')
                    ->orderBy('count', 'desc')
                    ->get()
                    ->pluck('count', 'category')
                    ->toArray();
            }
            
            return [
                'new_friendships' => $newFriendships,
                'friend_activity' => $friendActivity,
                'activity_types' => $activityTypes,
                'friend_categories' => $friendCategories,
                'total_friends' => count($friendIds),
                'time_period' => $this->timePeriod,
            ];
        });
    }
    
    /**
     * Render the component
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        $analyticsData = $this->getAnalyticsData();
        
        return view('livewire.common.friend-analytics', [
            'analyticsData' => $analyticsData,
            'entityType' => $this->entityType,
            'entityId' => $this->entityId,
        ]);
    }
}
