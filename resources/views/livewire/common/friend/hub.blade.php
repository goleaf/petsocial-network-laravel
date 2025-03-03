<div>
    <div class="friend-hub-container">
        <div class="card">
            <div class="card-header">
                <h5>{{ $entityType === 'pet' ? __('friends.pet_friend_hub') : __('friends.friend_hub') }}</h5>
            </div>
            <div class="card-body">
                <div class="stats-overview row mb-4">
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-value">{{ $stats['total_friends'] }}</div>
                            <div class="stat-label">{{ __('friends.total') }} {{ $entityType === 'pet' ? __('friends.pet_friends') : __('friends.friends') }}</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-value">{{ $stats['pending_sent'] }}</div>
                            <div class="stat-label">{{ __('friends.sent_requests') }}</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-value">{{ $stats['pending_received'] }}</div>
                            <div class="stat-label">{{ __('friends.received_requests') }}</div>
                        </div>
                    </div>
                    @if($entityType === 'user')
                        <div class="col-md-3">
                            <div class="stat-card">
                                <div class="stat-value">{{ $stats['followers'] ?? 0 }}</div>
                                <div class="stat-label">{{ __('friends.followers') }}</div>
                            </div>
                        </div>
                    @else
                        <div class="col-md-3">
                            <div class="stat-card">
                                <div class="stat-value">{{ count($stats['categories'] ?? []) }}</div>
                                <div class="stat-label">{{ __('friends.categories') }}</div>
                            </div>
                        </div>
                    @endif
                </div>
                
                <div class="nav-tabs-container mb-4">
                    <ul class="nav nav-tabs">
                        <li class="nav-item">
                            <a class="nav-link {{ $activeTab === 'overview' ? 'active' : '' }}" 
                               wire:click.prevent="setActiveTab('overview')" href="#">
                                {{ __('friends.overview') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $activeTab === 'friends' ? 'active' : '' }}" 
                               wire:click.prevent="setActiveTab('friends')" href="#">
                                {{ $entityType === 'pet' ? __('friends.pet_friends') : __('friends.friends') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $activeTab === 'requests' ? 'active' : '' }}" 
                               wire:click.prevent="setActiveTab('requests')" href="#">
                                {{ __('friends.requests') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $activeTab === 'finder' ? 'active' : '' }}" 
                               wire:click.prevent="setActiveTab('finder')" href="#">
                                {{ __('friends.find') }} {{ $entityType === 'pet' ? __('friends.pets') : __('friends.friends') }}
                            </a>
                        </li>
                        @if($entityType === 'user')
                            <li class="nav-item">
                                <a class="nav-link {{ $activeTab === 'followers' ? 'active' : '' }}" 
                                   wire:click.prevent="setActiveTab('followers')" href="#">
                                    {{ __('friends.followers') }}
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
                                            <h6>{{ __('friends.recent_activity') }}</h6>
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
                                            <h6>{{ $entityType === 'pet' ? __('friends.pet_friend_suggestions') : __('friends.friend_suggestions') }}</h6>
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
                                                <h6>{{ __('friends.categories') }}</h6>
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
                                            <h6>{{ __('friends.received_requests') }} ({{ $stats['pending_received'] }})</h6>
                                        </div>
                                        <div class="card-body">
                                            <!-- Received requests component would go here -->
                                            <p class="text-center">{{ __('friends.received_requests_display') }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h6>{{ __('friends.sent_requests') }} ({{ $stats['pending_sent'] }})</h6>
                                        </div>
                                        <div class="card-body">
                                            <!-- Sent requests component would go here -->
                                            <p class="text-center">{{ __('friends.sent_requests_display') }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @elseif($activeTab === 'finder')
                        <div class="tab-pane active">
                            <!-- Friend finder component would go here -->
                            <p class="text-center">{{ __('friends.friend_finder_display') }}</p>
                        </div>
                    @elseif($activeTab === 'followers' && $entityType === 'user')
                        <div class="tab-pane active">
                            <!-- Followers component would go here -->
                            <p class="text-center">{{ __('friends.followers_display') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
