<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('follows.people_following', ['name' => $user->name]) }}
            </h2>
            <a href="{{ route('profile', $user) }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <x-icons.back class="h-5 w-5 mr-2" stroke-width="2" />
                {{ __('follows.back_to_profile') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    @if($following->isEmpty())
                        <div class="text-center py-8">
                            <x-icons.users class="h-12 w-12 mx-auto text-gray-400" stroke-width="2" />
                            <p class="mt-2 text-gray-500">{{ $user->name }} {{ __('follows.not_following_anyone') }}</p>
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
                                                        {{ __('follows.you') }}
                                                    </span>
                                                @elseif(auth()->user()->isFriendWith($followed))
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                        {{ __('follows.friend') }}
                                                    </span>
                                                @elseif(auth()->user()->isFollowing($followed))
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                        {{ __('follows.following_status') }}
                                                    </span>
                                                @endif
                                            </div>
                                            
                                            @if($followed->location)
                                                <p class="text-xs text-gray-500 mt-1">{{ $followed->location }}</p>
                                            @endif
                                            
                                            <div class="mt-3 flex flex-wrap gap-2">
                                                @if(!auth()->user()->is($followed))
                                                    @livewire('common.follow.button', ['entityType' => 'user', 'entityId' => auth()->id(), 'targetId' => $followed->id], key('follow-'.$followed->id))
                                                    
                                                    @livewire('common.friend.button', ['entityType' => 'user', 'entityId' => auth()->id(), 'targetId' => $followed->id], key('friend-'.$followed->id))
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
