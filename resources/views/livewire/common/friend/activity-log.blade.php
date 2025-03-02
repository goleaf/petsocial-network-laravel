<div>
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
            <div class="flex flex-col sm:flex-row justify-between sm:items-center">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-3 sm:mb-0">{{ $entityType === 'pet' ? 'Pet' : '' }} Activity Log</h3>
                <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2">
                    <select wire:model="typeFilter" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <option value="">All Activity Types</option>
                        @foreach($activityTypes as $type => $label)
                            <option value="{{ $type }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    <select wire:model="dateFilter" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <option value="">All Time</option>
                        <option value="today">Today</option>
                        <option value="week">This Week</option>
                        <option value="month">This Month</option>
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
                                <p class="mt-2 text-gray-500">No activities found for the selected filters.</p>
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
                                                        <x-icons.arrow-right class="h-4 w-4 mr-1" stroke-width="2" /> View Details
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
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Activity Stats</h3>
                        </div>
                        <div class="p-4">
                            <div class="space-y-3">
                                <div class="flex justify-between items-center py-2 border-b border-gray-200">
                                    <span class="text-sm text-gray-600">Total Activities</span>
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
                                <h3 class="text-lg leading-6 font-medium text-gray-900">{{ $entityType === 'pet' ? 'Pet ' : '' }}Friend Activities</h3>
                                <label class="inline-flex items-center cursor-pointer">
                                    <span class="mr-2 text-sm text-gray-600">Show</span>
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
