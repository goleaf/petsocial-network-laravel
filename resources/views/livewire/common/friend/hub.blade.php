<div>
    <div class="friend-hub-container">
        <div class="card">
            <div class="card-header">
                <h5>{{ $entityType === 'pet' ? 'Pet' : '' }} Friend Hub</h5>
            </div>
            <div class="card-body">
                <div class="stats-overview row mb-4">
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-value">{{ $stats['total_friends'] }}</div>
                            <div class="stat-label">Total {{ $entityType === 'pet' ? 'Pet ' : '' }}Friends</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-value">{{ $stats['pending_sent'] }}</div>
                            <div class="stat-label">Sent Requests</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-value">{{ $stats['pending_received'] }}</div>
                            <div class="stat-label">Received Requests</div>
                        </div>
                    </div>
                    @if($entityType === 'user')
                        <div class="col-md-3">
                            <div class="stat-card">
                                <div class="stat-value">{{ $stats['followers'] ?? 0 }}</div>
                                <div class="stat-label">Followers</div>
                            </div>
                        </div>
                    @else
                        <div class="col-md-3">
                            <div class="stat-card">
                                <div class="stat-value">{{ count($stats['categories'] ?? []) }}</div>
                                <div class="stat-label">Categories</div>
                            </div>
                        </div>
                    @endif
                </div>
                
                <div class="nav-tabs-container mb-4">
                    <ul class="nav nav-tabs">
                        <li class="nav-item">
                            <a class="nav-link {{ $activeTab === 'overview' ? 'active' : '' }}" 
                               wire:click.prevent="setActiveTab('overview')" href="#">
                                Overview
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $activeTab === 'friends' ? 'active' : '' }}" 
                               wire:click.prevent="setActiveTab('friends')" href="#">
                                {{ $entityType === 'pet' ? 'Pet ' : '' }}Friends
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $activeTab === 'requests' ? 'active' : '' }}" 
                               wire:click.prevent="setActiveTab('requests')" href="#">
                                Requests
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $activeTab === 'finder' ? 'active' : '' }}" 
                               wire:click.prevent="setActiveTab('finder')" href="#">
                                Find {{ $entityType === 'pet' ? 'Pets' : 'Friends' }}
                            </a>
                        </li>
                        @if($entityType === 'user')
                            <li class="nav-item">
                                <a class="nav-link {{ $activeTab === 'followers' ? 'active' : '' }}" 
                                   wire:click.prevent="setActiveTab('followers')" href="#">
                                    Followers
                                </a>
                            </li>
                        @endif
                    </ul>
                </div>
                
                <div class="tab-content">
                    @if($activeTab === 'overview')
                        <div class="tab-pane active">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="card mb-4">
                                        <div class="card-header">
                                            <h6>Recent Activity</h6>
                                        </div>
                                        <div class="card-body">
                                            @livewire('common.friend.activity-log', [
                                                'entityType' => $entityType, 
                                                'entityId' => $entityId
                                            ], key('activity-log-'.$entityId))
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card mb-4">
                                        <div class="card-header">
                                            <h6>{{ $entityType === 'pet' ? 'Pet ' : '' }}Friend Suggestions</h6>
                                        </div>
                                        <div class="card-body">
                                            @livewire('common.friend.suggestions', [
                                                'entityType' => $entityType, 
                                                'entityId' => $entityId
                                            ], key('suggestions-'.$entityId))
                                        </div>
                                    </div>
                                    
                                    @if(!empty($stats['categories']))
                                        <div class="card">
                                            <div class="card-header">
                                                <h6>Categories</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="categories-list">
                                                    @foreach($stats['categories'] as $category => $count)
                                                        <div class="category-item d-flex justify-content-between">
                                                            <span>{{ $category }}</span>
                                                            <span class="badge badge-primary">{{ $count }}</span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @elseif($activeTab === 'friends')
                        <div class="tab-pane active">
                            @livewire('common.friend.list', [
                                'entityType' => $entityType, 
                                'entityId' => $entityId
                            ], key('friends-list-'.$entityId))
                        </div>
                    @elseif($activeTab === 'requests')
                        <div class="tab-pane active">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h6>Received Requests ({{ $stats['pending_received'] }})</h6>
                                        </div>
                                        <div class="card-body">
                                            <!-- Received requests component would go here -->
                                            <p class="text-center">Received requests will be displayed here.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h6>Sent Requests ({{ $stats['pending_sent'] }})</h6>
                                        </div>
                                        <div class="card-body">
                                            <!-- Sent requests component would go here -->
                                            <p class="text-center">Sent requests will be displayed here.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @elseif($activeTab === 'finder')
                        <div class="tab-pane active">
                            <!-- Friend finder component would go here -->
                            <p class="text-center">Friend finder will be displayed here.</p>
                        </div>
                    @elseif($activeTab === 'followers' && $entityType === 'user')
                        <div class="tab-pane active">
                            <!-- Followers component would go here -->
                            <p class="text-center">Followers will be displayed here.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
