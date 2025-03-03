<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('follows.followers_and_following') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Followers Section -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('follows.followers') }}</h3>
                        
                        @if($followers->isEmpty())
                            <p class="text-gray-500">{{ __('follows.you_no_followers') }}</p>
                        @else
                            <div class="space-y-4">
                                @foreach($followers as $follower)
                                    <div class="flex items-center justify-between border-b border-gray-200 pb-3 last:border-0">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <img class="h-10 w-10 rounded-full" src="{{ $follower->profile_photo_url }}" alt="{{ $follower->name }}">
                                            </div>
                                            <div class="ml-4">
                                                <a href="{{ route('profile', $follower) }}" class="text-sm font-medium text-gray-900 hover:underline">{{ $follower->name }}</a>
                                                @if($follower->location)
                                                    <p class="text-xs text-gray-500">{{ $follower->location }}</p>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="flex space-x-2">
                                            @if(auth()->user()->isFriendWith($follower))
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    {{ __('follows.friend') }}
                                                </span>
                                            @elseif(auth()->user()->hasPendingFriendRequestFrom($follower))
                                                <form method="POST" action="{{ route('friendships.accept', ['friendship' => auth()->user()->getFriendshipRequestFrom($follower)->id]) }}">
                                                    @csrf
                                                    <button type="submit" class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                        {{ __('follows.accept_request') }}
                                                    </button>
                                                </form>
                                            @elseif(auth()->user()->hasSentFriendRequestTo($follower))
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                    {{ __('follows.request_sent') }}
                                                </span>
                                            @else
                                                <form method="POST" action="{{ route('friendships.request', ['user' => $follower->id]) }}">
                                                    @csrf
                                                    <button type="submit" class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                        {{ __('follows.add_friend') }}
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            
                            <div class="mt-4">
                                {{ $followers->links() }}
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Following Section -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('follows.following') }}</h3>
                        
                        @if($following->isEmpty())
                            <p class="text-gray-500">{{ __('follows.you_not_following') }}</p>
                        @else
                            <div class="space-y-4">
                                @foreach($following as $followed)
                                    <div class="flex items-center justify-between border-b border-gray-200 pb-3 last:border-0">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <img class="h-10 w-10 rounded-full" src="{{ $followed->profile_photo_url }}" alt="{{ $followed->name }}">
                                            </div>
                                            <div class="ml-4">
                                                <a href="{{ route('profile', $followed) }}" class="text-sm font-medium text-gray-900 hover:underline">{{ $followed->name }}</a>
                                                @if($followed->location)
                                                    <p class="text-xs text-gray-500">{{ $followed->location }}</p>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="flex space-x-2">
                                            <form method="POST" action="{{ route('follows.toggle-notifications', ['user' => $followed->id]) }}">
                                                @csrf
                                                <button type="submit" class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-gray-700 bg-gray-100 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                                                    @if(auth()->user()->isReceivingNotificationsFrom($followed))
                                                        <x-icons.notifications class="h-4 w-4 mr-1" stroke-width="2" />
                                                        {{ __('follows.mute') }}
                                                    @else
                                                        <x-icons.notifications class="h-4 w-4 mr-1" stroke-width="2" />
                                                        {{ __('follows.unmute') }}
                                                    @endif
                                                </button>
                                            </form>
                                            
                                            @livewire('common.user.follow-button', ['userId' => $followed->id], key('follow-'.$followed->id))
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            
                            <div class="mt-4">
                                {{ $following->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            
            <div class="mt-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('follows.find_people') }}</h3>
                        <a href="{{ route('follows.recommendations') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <x-icons.search class="h-4 w-4 mr-1" stroke-width="2" /> {{ __('follows.view_recommendations') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
