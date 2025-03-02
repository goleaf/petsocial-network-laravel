@extends('layouts.app')
@section('content')
    <div class="max-w-lg mx-auto bg-white p-6 rounded-lg shadow">
        @if (session()->has('message'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
                {{ session('message') }}
            </div>
        @endif

        @if (session()->has('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                {{ session('error') }}
            </div>
        @endif

        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold">{{ $user->name }}'s Profile</h1>
            @if ($user->id === auth()->id())
                <a href="{{ route('profile.edit') }}" class="px-4 py-2 bg-blue-100 hover:bg-blue-200 text-blue-700 rounded-lg text-sm">
                    Edit Profile
                </a>
            @endif
        </div>

        <div class="flex flex-col md:flex-row md:items-start gap-6">
            <div class="flex-shrink-0">
                @if ($user->profile->avatar)
                    <img src="{{ Storage::url($user->profile->avatar) }}" class="w-32 h-32 rounded-full object-cover">
                @else
                    <div class="w-32 h-32 rounded-full bg-gray-200 flex items-center justify-center">
                        <span class="text-gray-600 font-bold text-2xl">{{ substr($user->name, 0, 1) }}</span>
                    </div>
                @endif

                <div class="mt-4 space-y-2">
                    @if ($user->id !== auth()->id())
                        @livewire('common.friend.button', ['entityType' => 'user', 'entityId' => auth()->id(), 'targetId' => $user->id], key('friend-'.$user->id))
                        @livewire('common.follow.button', ['entityType' => 'user', 'entityId' => auth()->id(), 'targetId' => $user->id], key('follow-'.$user->id))
                    @endif
                    
                    @if ($user->id === auth()->id() || auth()->user()->isAdmin())
                        <a href="{{ route('activity', $user) }}" class="block text-blue-500 hover:underline text-sm">
                            View Activity Logs
                        </a>
                    @endif
                </div>
            </div>

            <div class="flex-grow">
                <div class="mb-6">
                    <h2 class="text-lg font-semibold mb-2">About</h2>
                    <p class="text-gray-700">{{ $user->profile->bio ?: 'No bio available' }}</p>
                    @if ($user->profile->location)
                        <p class="text-gray-500 mt-2">📍 {{ $user->profile->location }}</p>
                    @endif
                </div>

                <div class="grid grid-cols-2 gap-4 text-center">
                    <div class="bg-gray-50 p-3 rounded-lg">
                        <div class="text-2xl font-bold">{{ $user->acceptedFriendships()->count() }}</div>
                        <div class="text-gray-500 text-sm">Friends</div>
                    </div>
                    <div class="bg-gray-50 p-3 rounded-lg">
                        <div class="text-2xl font-bold">{{ $user->pets()->count() }}</div>
                        <div class="text-gray-500 text-sm">Pets</div>
                    </div>
                    <div class="bg-gray-50 p-3 rounded-lg">
                        <div class="text-2xl font-bold">{{ $user->followers()->count() }}</div>
                        <div class="text-gray-500 text-sm">Followers</div>
                    </div>
                    <div class="bg-gray-50 p-3 rounded-lg">
                        <div class="text-2xl font-bold">{{ $user->following()->count() }}</div>
                        <div class="text-gray-500 text-sm">Following</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-8">
            <h2 class="text-xl font-semibold mb-4">Friends</h2>
            @if ($user->acceptedFriendships()->count() > 0)
                <ul class="grid grid-cols-2 md:grid-cols-3 gap-3">
                    @foreach ($user->friends() as $friend)
                        <li class="bg-gray-50 p-3 rounded-lg flex flex-col items-center">
                            <div class="h-12 w-12 rounded-full bg-gray-200 flex items-center justify-center mb-2">
                                <span class="text-gray-600 font-bold">{{ substr($friend->name, 0, 1) }}</span>
                            </div>
                            <a href="{{ route('profile', $friend) }}" class="text-blue-500 hover:underline text-sm font-medium">{{ $friend->name }}</a>
                            @if (isset($friend->pivot->category) && $friend->pivot->category)
                                <span class="text-xs bg-gray-200 px-2 py-1 rounded-full mt-1">{{ $friend->pivot->category }}</span>
                            @endif
                        </li>
                    @endforeach
                </ul>
                <div class="mt-2 text-right">
                    <a href="{{ route('friends') }}" class="text-blue-500 hover:underline text-sm">View All</a>
                </div>
            @else
                <p class="text-gray-500">No friends yet.</p>
            @endif
        </div>

        @if ($user->id !== auth()->id() && auth()->user()->acceptedFriendships()->count() > 0)
            <div class="mt-8">
                <h2 class="text-xl font-semibold mb-4">Mutual Friends</h2>
                @php 
                    $userFriends = $user->friends()->pluck('id');
                    $myFriends = auth()->user()->friends()->pluck('id');
                    $mutualFriendIds = $userFriends->intersect($myFriends);
                    $mutualFriends = \App\Models\User::whereIn('id', $mutualFriendIds)->get();
                @endphp
                
                @if ($mutualFriends->count() > 0)
                    <ul class="grid grid-cols-2 md:grid-cols-3 gap-3">
                        @foreach ($mutualFriends as $friend)
                            <li class="bg-gray-50 p-3 rounded-lg flex flex-col items-center">
                                <div class="h-12 w-12 rounded-full bg-gray-200 flex items-center justify-center mb-2">
                                    <span class="text-gray-600 font-bold">{{ substr($friend->name, 0, 1) }}</span>
                                </div>
                                <a href="{{ route('profile', $friend) }}" class="text-blue-500 hover:underline text-sm font-medium">{{ $friend->name }}</a>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-gray-500">No mutual friends.</p>
                @endif
            </div>
        @endif

        @if ($user->id === auth()->id())
            <div class="mt-8">
                <h2 class="text-xl font-semibold mb-4">Friend Suggestions</h2>
                @php
                    $currentUser = auth()->user();
                    $myInterests = $currentUser->profile->interests ?? [];
                    $suggestions = \App\Models\User::where('id', '!=', $currentUser->id)
                        ->whereNotIn('id', $currentUser->friends()->pluck('id'))
                        ->whereHas('profile', function($query) use ($myInterests) {
                            if (!empty($myInterests)) {
                                $query->whereJsonContains('interests', $myInterests);
                            }
                        })
                        ->limit(3)
                        ->get();
                @endphp
                
                @if ($suggestions->count() > 0)
                    <ul class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        @foreach ($suggestions as $suggestion)
                            <li class="bg-blue-50 p-4 rounded-lg">
                                <div class="flex flex-col items-center">
                                    <div class="h-16 w-16 rounded-full bg-gray-200 flex items-center justify-center mb-2">
                                        <span class="text-gray-600 font-bold text-xl">{{ substr($suggestion->name, 0, 1) }}</span>
                                    </div>
                                    <a href="{{ route('profile', $suggestion) }}" class="text-blue-500 hover:underline font-medium">{{ $suggestion->name }}</a>
                                    <div class="text-xs text-gray-500 mt-1 text-center">Similar interests</div>
                                    <a href="{{ route('friendships.request', $suggestion) }}" class="mt-2 px-3 py-1 bg-blue-500 hover:bg-blue-600 text-white rounded-lg text-sm">
                                        Add Friend
                                    </a>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-gray-500">No suggestions available at this time.</p>
                @endif
            </div>
        @endif

        <div class="mt-8">
            <h2 class="text-xl font-semibold mb-4">Pets</h2>
            @if ($user->pets->count() > 0)
                <ul class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach ($user->pets as $pet)
                        <li class="bg-gray-50 p-4 rounded-lg">
                            <div class="flex items-center">
                                <div class="h-12 w-12 rounded-full bg-gray-200 flex items-center justify-center mr-3">
                                    <span class="text-gray-600 font-bold">{{ substr($pet->name, 0, 1) }}</span>
                                </div>
                                <div>
                                    <a href="{{ route('pet.friends', $pet->id) }}" class="text-blue-500 hover:underline font-medium">{{ $pet->name }}</a>
                                    <div class="text-sm text-gray-500">
                                        @if ($pet->type) {{ $pet->type }} @endif
                                        @if ($pet->breed) - {{ $pet->breed }} @endif
                                    </div>
                                    <div class="text-xs text-gray-500 mt-1">{{ $pet->allFriends()->count() }} friends</div>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="text-gray-500">No pets yet.</p>
            @endif
        </div>
    </div>
@endsection
