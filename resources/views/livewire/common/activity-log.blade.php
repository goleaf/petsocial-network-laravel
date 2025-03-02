<div>
    <h2>Activity Log</h2>
    <div class="activity-log-container">
        @foreach($activities as $activity)
            <div class="activity-item">
                <div class="activity-icon">
                    <!-- Icon based on activity type -->
                </div>
                <div class="activity-content">
                    <div class="activity-header">
                        <span class="activity-type">{{ $activity->type }}</span>
                        <span class="activity-time">{{ $activity->happened_at->diffForHumans() }}</span>
                    </div>
                    <div class="activity-description">
                        {{ $activity->description }}
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>