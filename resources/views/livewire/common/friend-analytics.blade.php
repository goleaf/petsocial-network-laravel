<div>
    <div class="card">
        <div class="card-header">
            <h5>{{ $entityType === 'pet' ? 'Pet' : '' }} Friend Analytics</h5>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="stat-card p-3 border rounded text-center">
                        <h3 class="stat-value">{{ $stats['total_friends'] }}</h3>
                        <p class="stat-label mb-0">Total Friends</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card p-3 border rounded text-center">
                        <h3 class="stat-value">{{ $stats['new_friends'] }}</h3>
                        <p class="stat-label mb-0">New Friends (Last 30 Days)</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card p-3 border rounded text-center">
                        <h3 class="stat-value">{{ $stats['pending_requests'] }}</h3>
                        <p class="stat-label mb-0">Pending Requests</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card p-3 border rounded text-center">
                        <h3 class="stat-value">{{ $stats['interaction_rate'] }}%</h3>
                        <p class="stat-label mb-0">Interaction Rate</p>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-8">
                    <div class="chart-container mb-4">
                        <h6 class="mb-3">Friend Growth Over Time</h6>
                        <div class="chart-wrapper" style="height: 300px;">
                            <canvas id="friendGrowthChart" wire:ignore></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="chart-container mb-4">
                        <h6 class="mb-3">Friend Categories</h6>
                        <div class="chart-wrapper" style="height: 300px;">
                            <canvas id="friendCategoriesChart" wire:ignore></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="chart-container mb-4">
                        <h6 class="mb-3">Interaction Frequency</h6>
                        <div class="chart-wrapper" style="height: 250px;">
                            <canvas id="interactionChart" wire:ignore></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="chart-container mb-4">
                        <h6 class="mb-3">Friend Activity Times</h6>
                        <div class="chart-wrapper" style="height: 250px;">
                            <canvas id="activityTimesChart" wire:ignore></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="top-friends mb-4">
                <h6 class="mb-3">Most Active Friends</h6>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Friend</th>
                                <th>Interactions</th>
                                <th>Last Active</th>
                                <th>Friendship Duration</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topFriends as $friend)
                                <tr>
                                    <td class="d-flex align-items-center">
                                        <img src="{{ $friend->avatar ?? '/images/default-avatar.png' }}" 
                                             class="rounded-circle mr-2" width="30" height="30">
                                        <span>{{ $friend->name }}</span>
                                    </td>
                                    <td>{{ $friend->interactions_count }}</td>
                                    <td>{{ $friend->last_active_at->diffForHumans() }}</td>
                                    <td>{{ $friend->friendship_duration }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="export-section text-right">
                <button class="btn btn-outline-primary" wire:click="exportAnalytics">
                    <i class="fas fa-download"></i> Export Analytics
                </button>
            </div>
        </div>
    </div>
    
    @push('scripts')
    <script>
        document.addEventListener('livewire:load', function() {
            // Initialize charts when data is available
            Livewire.on('chartsDataReady', function(data) {
                initFriendGrowthChart(data.growthData);
                initFriendCategoriesChart(data.categoriesData);
                initInteractionChart(data.interactionData);
                initActivityTimesChart(data.activityTimesData);
            });
            
            function initFriendGrowthChart(data) {
                const ctx = document.getElementById('friendGrowthChart').getContext('2d');
                new Chart(ctx, {
                    type: 'line',
                    data: data,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false
                    }
                });
            }
            
            function initFriendCategoriesChart(data) {
                const ctx = document.getElementById('friendCategoriesChart').getContext('2d');
                new Chart(ctx, {
                    type: 'doughnut',
                    data: data,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false
                    }
                });
            }
            
            function initInteractionChart(data) {
                const ctx = document.getElementById('interactionChart').getContext('2d');
                new Chart(ctx, {
                    type: 'bar',
                    data: data,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false
                    }
                });
            }
            
            function initActivityTimesChart(data) {
                const ctx = document.getElementById('activityTimesChart').getContext('2d');
                new Chart(ctx, {
                    type: 'radar',
                    data: data,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false
                    }
                });
            }
        });
    </script>
    @endpush
</div>