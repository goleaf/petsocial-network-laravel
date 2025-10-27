<div>
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
            <div class="flex flex-col sm:flex-row justify-between sm:items-center">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-3 sm:mb-0">{{ $entityType === 'pet' ? __('friends.pet_activity_log') : __('friends.activity_log') }}</h3>
                <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2">
                    <select wire:model="typeFilter" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <option value="">{{ __('friends.all_activity_types') }}</option>
                        @foreach($activityTypes as $type => $label)
                            <option value="{{ $type }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    <select wire:model="dateFilter" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <option value="">{{ __('friends.all_time') }}</option>
                        <option value="today">{{ __('friends.today') }}</option>
                        <option value="week">{{ __('friends.this_week') }}</option>
                        <option value="month">{{ __('friends.this_month') }}</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="p-4 sm:p-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2">
                    <div class="space-y-4">
                        @if($activities->isEmpty())
                            <div class="text-center py-8 bg-gray-50 rounded-lg">
                                <x-icons.activity class="h-12 w-12 mx-auto text-gray-400" stroke-width="2" />
                                <p class="mt-2 text-gray-500">{{ __('friends.no_activities_found') }}</p>
                            </div>
                        @else
                            @foreach($activities as $activity)
                                <div class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0 mr-3">
                                            <div class="w-10 h-10 rounded-full flex items-center justify-center bg-gray-100">
                                                @switch($activity->activity_type)
                                                    @case('friend_request')
                                                        <x-icons.friends class="h-5 w-5 text-blue-500" stroke-width="2" />
                                                        @break
                                                    @case('friend_accept')
                                                        <x-icons.user class="h-5 w-5 text-green-500" stroke-width="2" />
                                                        @break
                                                    @case('post')
                                                        <x-icons.activity class="h-5 w-5 text-purple-500" stroke-width="2" />
                                                        @break
                                                    @case('like')
                                                <x-icons.activity class="h-5 w-5 text-red-500" stroke-width="2" />
                                                @break
                                            @case('comment')
                                                <x-icons.activity class="h-5 w-5 text-blue-500" stroke-width="2" />
                                                @break
                                            @default
                                                <x-icons.activity class="h-5 w-5 text-yellow-500" stroke-width="2" />
                                        @endswitch
                                    </div>
                                    <div class="activity-content">
                                        <div class="activity-header flex flex-wrap items-center gap-2">
                                            <span class="activity-type text-sm font-medium text-gray-900">{{ $activityTypes[$activity->activity_type] ?? $activity->activity_type }}</span>
                                            @if(($activity->severity ?? 'info') !== 'info')
                                                @php
                                                    $severityClasses = match($activity->severity) {
                                                        'warning' => 'bg-yellow-100 text-yellow-800',
                                                        'critical' => 'bg-red-100 text-red-800',
                                                        default => 'bg-blue-100 text-blue-800',
                                                    };
                                                @endphp
                                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $severityClasses }}">
                                                    {{ __('friends.activity_severity_' . $activity->severity) }}
                                                </span>
                                            @endif
                                            <span class="activity-time ml-auto text-xs text-gray-500">{{ $activity->created_at->diffForHumans() }}</span>
                                        </div>
                                        <div class="activity-description">
                                            {!! $activity->description !!}
                                        </div>
                                        @if(is_array($activity->metadata) && !empty($activity->metadata))
                                            <div class="activity-metadata mt-2 space-y-2 text-xs text-gray-500">
                                                @if(isset($activity->metadata['preview']))
                                                    <p class="text-gray-600">{{ \Illuminate\Support\Str::limit($activity->metadata['preview'], 140) }}</p>
                                                @endif
                                                @if(isset($activity->metadata['ip_address']))
                                                    <div class="flex items-center space-x-1">
                                                        <span class="font-medium">{{ __('friends.activity_metadata_ip') }}</span>
                                                        <span>{{ $activity->metadata['ip_address'] }}</span>
                                                    </div>
                                                @endif
                                                @if(isset($activity->metadata['user_agent']))
                                                    <div class="flex items-center space-x-1">
                                                        <span class="font-medium">{{ __('friends.activity_metadata_device') }}</span>
                                                        <span>{{ $activity->metadata['user_agent'] }}</span>
                                                    </div>
                                                @endif
                                                @if(isset($activity->metadata['link']))
                                                    <a href="{{ $activity->metadata['link'] }}" class="inline-flex items-center text-indigo-600 hover:text-indigo-800">
                                                        <x-icons.arrow-right class="h-4 w-4 mr-1" stroke-width="2" /> {{ __('friends.view_details') }}
                                                    </a>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                            
                            <div class="mt-4">
                                {{ $activities->links() }}
                            </div>
                        @endif
                    </div>
                </div>
                <div>
                    <div class="bg-white shadow rounded-lg overflow-hidden mb-6">
                        <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">{{ __('friends.activity_stats') }}</h3>
                        </div>
                        <div class="p-4">
                            <div class="space-y-3">
                                <div class="flex justify-between items-center py-2 border-b border-gray-200">
                                    <span class="text-sm text-gray-600">{{ __('friends.total_activities') }}</span>
                                    <span class="font-medium text-gray-900">{{ $stats['total'] }}</span>
                                </div>
                                @foreach($stats['by_type'] as $type => $count)
                                    <div class="flex justify-between items-center py-2 border-b border-gray-200">
                                        <span class="text-sm text-gray-600">{{ $activityTypes[$type] ?? $type }}</span>
                                        <span class="font-medium text-gray-900">{{ $count }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    
                    @if($showFriendActivities && $friendActivities->isNotEmpty())
                        <div class="bg-white shadow rounded-lg overflow-hidden mb-6">
                            <div class="px-4 py-5 sm:px-6 border-b border-gray-200 flex justify-between items-center">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">{{ $entityType === 'pet' ? __('friends.pet_friend_activities') : __('friends.friend_activities') }}</h3>
                                <label class="inline-flex items-center cursor-pointer">
                                    <span class="mr-2 text-sm text-gray-600">{{ __('friends.show') }}</span>
                                    <div class="relative">
                                        <input type="checkbox" wire:model="showFriendActivities" class="sr-only peer">
                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                    </div>
                                </label>
                            </div>
                            <div class="p-4">
                                <div class="space-y-4">
                                    @foreach($friendActivities as $activity)
                                        <div class="border-b border-gray-200 pb-3 last:border-0 last:pb-0">
                                            <div class="flex items-start">
                                                <div class="flex-1">
                                                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center">
                                                        <p class="text-sm font-medium text-gray-900">
                                                            {{ $entityType === 'pet' ? $activity->pet->name : $activity->user->name }}
                                                        </p>
                                                        <div class="flex space-x-2 text-xs text-gray-500">
                                                            <span>{{ $activityTypes[$activity->activity_type] ?? $activity->activity_type }}</span>
                                                            <span>{{ $activity->created_at->diffForHumans() }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="mt-1 text-sm text-gray-700">
                                                        {!! $activity->description !!}
                                                    </div>
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
