<div class="bg-white p-6 rounded-lg shadow mb-4">
    <h2 class="text-xl font-semibold text-gray-800 mb-4">Friend Suggestions</h2>
    @if ($suggestions->isEmpty())
        <p class="text-gray-500">No suggestions right now.</p>
    @else
        <ul class="space-y-4">
            @foreach ($suggestions as $suggestion)
                <li class="bg-gray-50 p-4 rounded-lg">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center">
                            <div class="h-12 w-12 rounded-full bg-gray-200 flex items-center justify-center mr-3">
                                <span class="text-gray-600 font-bold">{{ substr($suggestion['user']->name, 0, 1) }}</span>
                            </div>
                            <div>
                                <a href="{{ route('profile', $suggestion['user']) }}" class="text-blue-500 hover:underline font-medium">{{ $suggestion['user']->name }}</a>
                                <div class="text-sm text-gray-500 mt-1">
                                    @if ($suggestion['mutual_friend_count'] > 0)
                                        <div class="flex items-center mb-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                            </svg>
                                            {{ $suggestion['mutual_friend_count'] }} mutual {{ Str::plural('friend', $suggestion['mutual_friend_count']) }}
                                        </div>
                                    @endif
                                    
                                    @if ($suggestion['mutual_interest_count'] > 0)
                                        <div class="flex items-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                                            </svg>
                                            {{ $suggestion['mutual_interest_count'] }} shared {{ Str::plural('interest', $suggestion['mutual_interest_count']) }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div>
                            @livewire('social.friend.button', ['userId' => $suggestion['user']->id], key('friend-suggestion-'.$suggestion['user']->id))
                        </div>
                    </div>
                    
                    @if ($suggestion['mutual_friends']->isNotEmpty())
                        <div class="mt-3 pl-14">
                            <div class="text-xs text-gray-500 mb-1">Mutual friends:</div>
                            <div class="flex space-x-2">
                                @foreach ($suggestion['mutual_friends'] as $friend)
                                    <a href="{{ route('profile', $friend) }}" class="text-xs text-blue-500 hover:underline">{{ $friend->name }}</a>
                                    @if (!$loop->last)
                                        <span class="text-gray-400">•</span>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif
                </li>
            @endforeach
        </ul>
    @endif
</div>
