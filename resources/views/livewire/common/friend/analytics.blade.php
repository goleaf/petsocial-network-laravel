<div>
    <!-- Analytics Wrapper -->
    <div class="friend-analytics-container">
        <!-- Summary Section -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">{{ __('friends.analytics_overview') }}</h5>
                <span class="badge bg-primary">{{ strtoupper($entityType) }}</span>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="stat-card">
                            <!-- Total connections stat -->
                            <div class="stat-label">{{ __('friends.total_connections') }}</div>
                            <div class="stat-value">{{ $summary['total_friends'] ?? 0 }}</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card">
                            <!-- New connections stat -->
                            <div class="stat-label">{{ __('friends.new_connections_30_days') }}</div>
                            <div class="stat-value">{{ $summary['new_friends_last_30_days'] ?? 0 }}</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card">
                            <!-- Pending requests summary -->
                            <div class="stat-label">{{ __('friends.pending_requests_summary') }}</div>
                            <div class="stat-value">
                                {{ ($summary['pending_sent'] ?? 0) + ($summary['pending_received'] ?? 0) }}
                            </div>
                            <small class="text-muted">
                                {{ __('friends.pending_breakdown', [
                                    'sent' => $summary['pending_sent'] ?? 0,
                                    'received' => $summary['pending_received'] ?? 0
                                ]) }}
                            </small>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mt-1">
                    <div class="col-md-6">
                        <div class="stat-card">
                            <!-- Blocked connections stat -->
                            <div class="stat-label">{{ __('friends.blocked_connections') }}</div>
                            <div class="stat-value">{{ $summary['blocked'] ?? 0 }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="stat-card">
                            <!-- Average acceptance time stat -->
                            <div class="stat-label">{{ __('friends.avg_acceptance_time_hours') }}</div>
                            <div class="stat-value">
                                @if(!empty($summary['average_acceptance_hours']))
                                    {{ $summary['average_acceptance_hours'] }}
                                @else
                                    <span class="text-muted">{{ __('friends.not_enough_data') }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Analytics Section -->
        <div class="row g-4">
            <div class="col-lg-7">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">{{ __('friends.connection_trends') }}</h6>
                        <div class="d-flex align-items-center gap-2">
                            <!-- Timeframe selector -->
                            <label for="trend-range" class="form-label mb-0 small text-muted">{{ __('friends.select_timeframe') }}</label>
                            <select id="trend-range" wire:model="trendRange" class="form-select form-select-sm w-auto">
                                <option value="3_months">{{ __('friends.timeframe_3_months') }}</option>
                                <option value="6_months">{{ __('friends.timeframe_6_months') }}</option>
                                <option value="12_months">{{ __('friends.timeframe_12_months') }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="card-body">
                        @if(!empty($trendData))
                            <div class="trend-table-wrapper table-responsive">
                                <table class="table table-sm mb-0">
                                    <thead>
                                        <tr>
                                            <th>{{ __('friends.month') }}</th>
                                            <th class="text-end">{{ __('friends.new_connections') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($trendData as $period => $total)
                                            <tr>
                                                <td>{{ \Carbon\Carbon::createFromFormat('Y-m', $period)->translatedFormat('F Y') }}</td>
                                                <td class="text-end">{{ $total }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-muted mb-0">{{ __('friends.no_data_available') }}</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">{{ __('friends.category_breakdown') }}</h6>
                    </div>
                    <div class="card-body">
                        @if(!empty($categoryBreakdown))
                            <ul class="list-group list-group-flush">
                                @foreach($categoryBreakdown as $category => $total)
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span>{{ $category }}</span>
                                        <span class="badge bg-secondary">{{ $total }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-muted mb-0">{{ __('friends.no_data_available') }}</p>
                        @endif
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">{{ __('friends.mutual_connections_highlight') }}</h6>
                    </div>
                    <div class="card-body">
                        @if(!empty($mutualInsights))
                            <ul class="list-group list-group-flush">
                                @foreach($mutualInsights as $insight)
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <!-- Mutual connections row -->
                                        <span>{{ $insight['name'] }}</span>
                                        <span class="badge bg-info text-dark">{{ trans_choice('friends.mutual_count_label', $insight['mutual_count'], ['count' => $insight['mutual_count']]) }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-muted mb-0">{{ __('friends.mutual_connections_none') }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
