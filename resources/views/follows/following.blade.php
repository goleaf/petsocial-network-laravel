<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('People') }} {{ $user->name }} {{ __('is Following') }}
            </h2>
            <a href="{{ route('profile', $user) }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                {{ __('Back to Profile') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    @if($following->isEmpty())
                        <div class="text-center py-8">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <p class="mt-2 text-gray-500">{{ $user->name }} {{ __('isn\'t following anyone yet.') }}</p>
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($following as $followed)
                                <div class="border rounded-lg p-4 hover:shadow-md transition-shadow">
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0 h-12 w-12">
                                            <img class="h-12 w-12 rounded-full" src="{{ $followed->profile_photo_url }}" alt="{{ $followed->name }}">
                                        </div>
                                        <div class="ml-4 flex-1">
                                            <div class="flex justify-between">
                                                <a href="{{ route('profile', $followed) }}" class="text-sm font-medium text-gray-900 hover:underline">{{ $followed->name }}</a>
                                                
                                                @if(auth()->user()->is($followed))
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                        {{ __('You') }}
                                                    </span>
                                                @elseif(auth()->user()->isFriendWith($followed))
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                        {{ __('Friend') }}
                                                    </span>
                                                @elseif(auth()->user()->isFollowing($followed))
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                        {{ __('Following') }}
                                                    </span>
                                                @endif
                                            </div>
                                            
                                            @if($followed->location)
                                                <p class="text-xs text-gray-500 mt-1">{{ $followed->location }}</p>
                                            @endif
                                            
                                            <div class="mt-3 flex flex-wrap gap-2">
                                                @if(!auth()->user()->is($followed))
                                                    @if(!auth()->user()->isFollowing($followed))
                                                        <form method="POST" action="{{ route('follows.follow', ['user' => $followed->id]) }}">
                                                            @csrf
                                                            <button type="submit" class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                                {{ __('Follow') }}
                                                            </button>
                                                        </form>
                                                    @else
                                                        <form method="POST" action="{{ route('follows.unfollow', ['user' => $followed->id]) }}">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                                                {{ __('Unfollow') }}
                                                            </button>
                                                        </form>
                                                    @endif
                                                    
                                                    @if(!auth()->user()->isFriendWith($followed) && !auth()->user()->hasSentFriendRequestTo($followed) && !auth()->user()->hasPendingFriendRequestFrom($followed))
                                                        <form method="POST" action="{{ route('friendships.request', ['user' => $followed->id]) }}">
                                                            @csrf
                                                            <button type="submit" class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                                {{ __('Add Friend') }}
                                                            </button>
                                                        </form>
                                                    @elseif(auth()->user()->hasPendingFriendRequestFrom($followed))
                                                        <form method="POST" action="{{ route('friendships.accept', ['friendship' => auth()->user()->getFriendshipRequestFrom($followed)->id]) }}">
                                                            @csrf
                                                            <button type="submit" class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                                                {{ __('Accept Request') }}
                                                            </button>
                                                        </form>
                                                    @elseif(auth()->user()->hasSentFriendRequestTo($followed))
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                            {{ __('Request Sent') }}
                                                        </span>
                                                    @endif
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        <div class="mt-6">
                            {{ $following->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
