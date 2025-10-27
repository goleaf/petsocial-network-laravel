@extends('layouts.app')
@section('content')
    @php
        $viewer = auth()->user();
    @endphp
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
            <h1 class="text-2xl font-bold">{{ __('profile.profile', ['name' => $user->name]) }}</h1>
            @if ($user->id === auth()->id())
                <a href="{{ route('profile.edit') }}" class="px-4 py-2 bg-blue-100 hover:bg-blue-200 text-blue-700 rounded-lg text-sm">
                    {{ __('profile.edit_profile') }}
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
                    
                    @php
                        $canViewActivity = $user->canViewPrivacySection($viewer, 'activity') || ($viewer && $viewer->isAdmin());
                    @endphp
                    @if ($canViewActivity)
                        <a href="{{ route('activity', ['entity_type' => 'user', 'entity_id' => $user->id]) }}" class="block px-4 py-2 bg-green-100 text-green-700 rounded-lg text-sm hover:bg-green-200 transition-colors duration-200 text-center">
                            <div class="flex items-center justify-center">
                                <x-icons.activity class="h-4 w-4 mr-1" />
                                {{ __('profile.view_activity_logs') }}
                            </div>
                        </a>
                    @elseif ($viewer && $viewer->id !== $user->id)
                        <p class="text-xs text-gray-500 text-center">{{ __('profile.activity_private') }}</p>
                    @endif
                </div>
            </div>

            <div class="flex-grow">
                <div class="mb-6">
                    <h2 class="text-lg font-semibold mb-2">{{ __('profile.about') }}</h2>
                    @if ($user->canViewPrivacySection($viewer, 'basic_info'))
                        <p class="text-gray-700">{{ $user->profile->bio ?: __('profile.no_bio') }}</p>
                        @if ($user->profile->location)
                            <p class="text-gray-500 mt-2">📍 {{ $user->profile->location }}</p>
                        @endif
                    @else
                        <p class="text-gray-500 italic">{{ __('profile.section_private') }}</p>
                    @endif
                </div>

                @if ($user->canViewPrivacySection($viewer, 'stats'))
                    <div class="grid grid-cols-2 gap-4 text-center">
                        <div class="bg-gray-50 p-3 rounded-lg">
                            <div class="text-2xl font-bold">{{ $user->acceptedFriendships()->count() }}</div>
                            <div class="text-gray-500 text-sm">{{ __('profile.friends') }}</div>
                        </div>
                        <div class="bg-gray-50 p-3 rounded-lg">
                            <div class="text-2xl font-bold">{{ $user->pets()->count() }}</div>
                            <div class="text-gray-500 text-sm">{{ __('profile.pets') }}</div>
                        </div>
                        <div class="bg-gray-50 p-3 rounded-lg">
                            <div class="text-2xl font-bold">{{ $user->followers()->count() }}</div>
                            <div class="text-gray-500 text-sm">{{ __('profile.followers') }}</div>
                        </div>
                        <div class="bg-gray-50 p-3 rounded-lg">
                            <div class="text-2xl font-bold">{{ $user->following()->count() }}</div>
                            <div class="text-gray-500 text-sm">{{ __('profile.following') }}</div>
                        </div>
                    </div>
                @else
                    <div class="bg-gray-50 p-3 rounded-lg text-center">
                        <p class="text-gray-500 italic">{{ __('profile.section_private') }}</p>
                    </div>
                @endif
            </div>
        </div>

        <div class="mt-8">
            <h2 class="text-xl font-semibold mb-4">{{ __('profile.friends') }}</h2>
            @if ($user->canViewPrivacySection($viewer, 'friends'))
                @if ($user->acceptedFriendships()->count() > 0)
                    <div class="flex justify-between items-center mb-4">
                        <span class="text-sm text-gray-500">{{ __('profile.friend_count', ['count' => $user->acceptedFriendships()->count(), 'friends' => Str::plural('friend', $user->acceptedFriendships()->count())]) }}</span>
                        <a href="{{ route('friendships.index') }}" class="text-sm text-blue-500 hover:underline">{{ __('profile.view_all') }}</a>
                    </div>
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
                        <a href="{{ route('friends') }}" class="text-blue-500 hover:underline text-sm">{{ __('profile.view_all') }}</a>
                    </div>
                @else
                    <p class="text-gray-500">{{ __('profile.no_friends') }}</p>
                @endif
            @else
                <p class="text-gray-500 italic">{{ __('profile.section_private') }}</p>
            @endif
        </div>

        @if ($user->id !== auth()->id() && auth()->user()->acceptedFriendships()->count() > 0)
            <div class="mt-8">
                <h2 class="text-xl font-semibold mb-4">{{ __('profile.mutual_friends') }}</h2>
                @if ($user->canViewPrivacySection($viewer, 'mutual_friends'))
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
                        <p class="text-gray-500">{{ __('profile.no_mutual_friends') }}</p>
                    @endif
                @else
                    <p class="text-gray-500 italic">{{ __('profile.section_private') }}</p>
                @endif
            </div>
        @endif

        @if ($user->id === auth()->id())
            <div class="mt-8">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold">{{ __('profile.friend_suggestions') }}</h2>
                    <a href="{{ route('friendships.suggestions') }}" class="text-sm text-blue-500 hover:underline">{{ __('profile.view_all') }}</a>
                </div>
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
                                    <div class="text-xs text-gray-500 mt-1 text-center">{{ __('profile.similar_interests') }}</div>
                                    <a href="{{ route('friendships.request', $suggestion) }}" class="mt-2 px-3 py-1 bg-blue-500 hover:bg-blue-600 text-white rounded-lg text-sm">
                                        {{ __('profile.add_friend') }}
                                    </a>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-gray-500">{{ __('profile.no_suggestions') }}</p>
                @endif
            </div>
        @endif

        <div class="mt-8">
            <h2 class="text-xl font-semibold mb-4">{{ __('profile.pets') }}</h2>
            @if ($user->canViewPrivacySection($viewer, 'pets'))
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
                                        <div class="text-xs text-gray-500 mt-1">{{ $pet->allFriends()->count() }} {{ __('profile.friends') }}</div>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-gray-500">{{ __('profile.no_pets') }}</p>
                @endif
            @else
                <p class="text-gray-500 italic">{{ __('profile.section_private') }}</p>
            @endif
        </div>
    </div>
@endsection
