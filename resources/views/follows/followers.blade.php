<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $user->name }}'s {{ __('Followers') }}
            </h2>
            <a href="{{ route('profile', $user) }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <x-icons.back class="h-5 w-5 mr-2" />
                {{ __('Back to Profile') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    @if($followers->isEmpty())
                        <div class="text-center py-8">
                            <x-icons.users class="h-12 w-12 mx-auto text-gray-400" stroke-width="2" />
                            <p class="mt-2 text-gray-500">{{ $user->name }} {{ __('doesn\'t have any followers yet.') }}</p>
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($followers as $follower)
                                <div class="border rounded-lg p-4 hover:shadow-md transition-shadow">
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0 h-12 w-12">
                                            <img class="h-12 w-12 rounded-full" src="{{ $follower->profile_photo_url }}" alt="{{ $follower->name }}">
                                        </div>
                                        <div class="ml-4 flex-1">
                                            <div class="flex justify-between">
                                                <a href="{{ route('profile', $follower) }}" class="text-sm font-medium text-gray-900 hover:underline">{{ $follower->name }}</a>
                                                
                                                @if(auth()->user()->is($follower))
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                        {{ __('You') }}
                                                    </span>
                                                @elseif(auth()->user()->isFriendWith($follower))
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                        {{ __('Friend') }}
                                                    </span>
                                                @elseif(auth()->user()->isFollowing($follower))
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                        {{ __('Following') }}
                                                    </span>
                                                @endif
                                            </div>
                                            
                                            @if($follower->location)
                                                <p class="text-xs text-gray-500 mt-1">{{ $follower->location }}</p>
                                            @endif
                                            
                                            <div class="mt-3 flex flex-wrap gap-2">
                                                @if(!auth()->user()->is($follower))
                                                    @livewire('common.follow.button', ['entityType' => 'user', 'entityId' => auth()->id(), 'targetId' => $follower->id], key('follow-'.$follower->id))
                                                    
                                                    @livewire('common.friend.button', ['entityType' => 'user', 'entityId' => auth()->id(), 'targetId' => $follower->id], key('friend-'.$follower->id))
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        <div class="mt-6">
                            {{ $followers->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
