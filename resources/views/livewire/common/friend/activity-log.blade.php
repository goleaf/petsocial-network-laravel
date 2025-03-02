<div>
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5>{{ $entityType === 'pet' ? 'Pet' : '' }} Activity Log</h5>
                <div class="filter-controls">
                    <div class="btn-group">
                        <select wire:model="typeFilter" class="form-control">
                            <option value="">All Activity Types</option>
                            @foreach($activityTypes as $type => $label)
                                <option value="{{ $type }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <select wire:model="dateFilter" class="form-control ml-2">
                            <option value="">All Time</option>
                            <option value="today">Today</option>
                            <option value="week">This Week</option>
                            <option value="month">This Month</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <div class="activity-feed">
                        @if($activities->isEmpty())
                            <div class="text-center py-4">
                                <p>No activities found for the selected filters.</p>
                            </div>
                        @else
                            @foreach($activities as $activity)
                                <div class="activity-item">
                                    <div class="activity-icon">
                                        @switch($activity->activity_type)
                                            @case('friend_request')
                                                <i class="fas fa-user-plus"></i>
                                                @break
                                            @case('friend_accept')
                                                <i class="fas fa-handshake"></i>
                                                @break
                                            @case('post')
                                                <i class="fas fa-comment-alt"></i>
                                                @break
                                            @case('like')
                                                <i class="fas fa-heart"></i>
                                                @break
                                            @case('comment')
                                                <i class="fas fa-comments"></i>
                                                @break
                                            @default
                                                <i class="fas fa-star"></i>
                                        @endswitch
                                    </div>
                                    <div class="activity-content">
                                        <div class="activity-header">
                                            <span class="activity-type">{{ $activityTypes[$activity->activity_type] ?? $activity->activity_type }}</span>
                                            <span class="activity-time">{{ $activity->created_at->diffForHumans() }}</span>
                                        </div>
                                        <div class="activity-description">
                                            {!! $activity->description !!}
                                        </div>
                                        @if($activity->metadata)
                                            <div class="activity-metadata">
                                                @if(isset($activity->metadata['link']))
                                                    <a href="{{ $activity->metadata['link'] }}" class="btn btn-sm btn-outline-primary">
                                                        View Details
                                                    </a>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                            
                            <div class="pagination-container mt-4">
                                {{ $activities->links() }}
                            </div>
                        @endif
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="mb-0">Activity Stats</h6>
                        </div>
                        <div class="card-body">
                            <div class="stats-item">
                                <span class="stats-label">Total Activities</span>
                                <span class="stats-value">{{ $stats['total'] }}</span>
                            </div>
                            @foreach($stats['by_type'] as $type => $count)
                                <div class="stats-item">
                                    <span class="stats-label">{{ $activityTypes[$type] ?? $type }}</span>
                                    <span class="stats-value">{{ $count }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    
                    @if($showFriendActivities && $friendActivities->isNotEmpty())
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">{{ $entityType === 'pet' ? 'Pet ' : '' }}Friend Activities</h6>
                                <div class="form-check">
                                    <input type="checkbox" wire:model="showFriendActivities" class="form-check-input" id="showFriendActivities">
                                    <label class="form-check-label" for="showFriendActivities">Show</label>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="friend-activity-feed">
                                    @foreach($friendActivities as $activity)
                                        <div class="activity-item small">
                                            <div class="activity-content">
                                                <div class="activity-header">
                                                    <span class="activity-entity">
                                                        {{ $entityType === 'pet' ? $activity->pet->name : $activity->user->name }}
                                                    </span>
                                                    <span class="activity-type">{{ $activityTypes[$activity->activity_type] ?? $activity->activity_type }}</span>
                                                    <span class="activity-time">{{ $activity->created_at->diffForHumans() }}</span>
                                                </div>
                                                <div class="activity-description">
                                                    {!! $activity->description !!}
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
