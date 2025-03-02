<div class="max-w-4xl mx-auto bg-white p-6 rounded-lg shadow">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Friend Activity</h1>
        <div class="flex space-x-2">
            <button wire:click="setTimeframe('day')" class="px-4 py-2 rounded-lg {{ $timeframe === 'day' ? 'bg-blue-500 text-white' : 'bg-gray-200' }}">
                Today
            </button>
            <button wire:click="setTimeframe('week')" class="px-4 py-2 rounded-lg {{ $timeframe === 'week' ? 'bg-blue-500 text-white' : 'bg-gray-200' }}">
                This Week
            </button>
            <button wire:click="setTimeframe('month')" class="px-4 py-2 rounded-lg {{ $timeframe === 'month' ? 'bg-blue-500 text-white' : 'bg-gray-200' }}">
                This Month
            </button>
        </div>
    </div>

    <div class="mb-4 flex space-x-4">
        <div class="bg-blue-50 p-3 rounded-lg flex-1 text-center">
            <span class="block text-2xl font-bold text-blue-700">{{ $postsCount }}</span>
            <span class="text-sm text-gray-600">Posts</span>
        </div>
        <div class="bg-green-50 p-3 rounded-lg flex-1 text-center">
            <span class="block text-2xl font-bold text-green-700">{{ $friendshipsCount }}</span>
            <span class="text-sm text-gray-600">New Friendships</span>
        </div>
    </div>

    @if (empty($activities))
        <div class="text-center py-8">
            <p class="text-gray-500">No activity from your friends in this time period.</p>
            <p class="text-sm text-gray-400 mt-2">Try changing the time filter or add more friends!</p>
        </div>
    @else
        <div class="divide-y divide-gray-200">
            @foreach ($activities as $activity)
                <div class="py-4">
                    @if ($activity['type'] === 'post')
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="flex items-center mb-3">
                                <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center mr-3">
                                    <span class="text-gray-600 font-bold">{{ substr($activity['user']->name, 0, 1) }}</span>
                                </div>
                                <div>
                                    <a href="{{ route('profile', $activity['user']) }}" class="text-blue-500 hover:underline font-medium">{{ $activity['user']->name }}</a>
                                    <div class="text-xs text-gray-500">{{ $activity['created_at']->diffForHumans() }}</div>
                                </div>
                            </div>
                            <div class="ml-13">
                                <p class="text-gray-800">{{ $activity['data']->content }}</p>
                                @if ($activity['data']->image)
                                    <div class="mt-2">
                                        <img src="{{ $activity['data']->image }}" alt="Post image" class="rounded-lg max-h-64">
                                    </div>
                                @endif
                                <div class="mt-3 flex space-x-4 text-sm text-gray-500">
                                    <span>{{ $activity['data']->likes_count ?? 0 }} likes</span>
                                    <span>{{ $activity['data']->comments_count ?? 0 }} comments</span>
                                </div>
                            </div>
                        </div>
                    @elseif ($activity['type'] === 'friendship')
                        <div class="bg-green-50 p-4 rounded-lg">
                            <div class="flex items-center">
                                <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center mr-2">
                                    <span class="text-gray-600 font-bold">{{ substr($activity['sender']->name, 0, 1) }}</span>
                                </div>
                                <a href="{{ route('profile', $activity['sender']) }}" class="text-blue-500 hover:underline font-medium">{{ $activity['sender']->name }}</a>
                                <span class="mx-2 text-gray-500">and</span>
                                <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center mr-2">
                                    <span class="text-gray-600 font-bold">{{ substr($activity['recipient']->name, 0, 1) }}</span>
                                </div>
                                <a href="{{ route('profile', $activity['recipient']) }}" class="text-blue-500 hover:underline font-medium">{{ $activity['recipient']->name }}</a>
                                <span class="ml-2 text-gray-500">became friends</span>
                            </div>
                            <div class="text-xs text-gray-500 mt-2 text-right">{{ $activity['created_at']->diffForHumans() }}</div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>
