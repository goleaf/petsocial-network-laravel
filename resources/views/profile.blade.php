@extends('layouts.app')
@section('content')
    <div class="max-w-lg mx-auto bg-white p-6 rounded-lg shadow">
        <h1 class="text-2xl font-bold mb-4 text-center">{{ $user->name }}'s Profile</h1>
        @if ($user->profile->avatar)
            <img src="{{ Storage::url($user->profile->avatar) }}" class="w-32 h-32 rounded-full mx-auto mb-4">
        @else
            <div class="w-32 h-32 rounded-full bg-gray-200 mx-auto mb-4 flex items-center justify-center text-gray-500">No Photo</div>
        @endif
        <p class="text-center text-gray-700">{{ $user->profile->bio }}</p>
        @if ($user->profile->location)
            <p class="text-center text-gray-500 mt-2">📍 {{ $user->profile->location }}</p>
        @endif
        <div class="flex justify-center gap-4 mt-4">
            @if ($user->id !== auth()->id())
                @livewire('friend-button', ['userId' => $user->id], key('friend-'.$user->id))
                @livewire('follow-button', ['userId' => $user->id], key('follow-'.$user->id))
                @livewire('block-button', ['userId' => $user->id], key('block-'.$user->id))
            @endif
            @if ($user->id === auth()->id() || auth()->user()->isAdmin())
                <a href="{{ route('activity', $user) }}" class="text-blue-500 hover:underline">View Activity Logs</a>
            @endif
        </div>
        <h2 class="text-xl font-semibold mt-6">Friends ({{ $user->friends->count() }})</h2>
        <ul class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-2">
            @foreach ($user->friends as $friend)
                <li><a href="{{ route('profile', $friend) }}" class="text-blue-500 hover:underline">{{ $friend->name }}</a></li>
            @endforeach
        </ul>
        @if ($user->id !== auth()->id())
            @php $mutualFriends = auth()->user()->friends->intersect($user->friends); @endphp
            <h2 class="text-xl font-semibold mt-6">Mutual Friends ({{ $mutualFriends->count() }})</h2>
            <ul class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-2">
                @foreach ($mutualFriends as $friend)
                    <li><a href="{{ route('profile', $friend) }}" class="text-blue-500 hover:underline">{{ $friend->name }}</a></li>
                @endforeach
            </ul>
        @endif
        <h2 class="text-xl font-semibold mt-6">Following ({{ $user->following->count() }})</h2>
        <ul class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-2">
            @foreach ($user->following as $following)
                <li><a href="{{ route('profile', $following) }}" class="text-blue-500 hover:underline">{{ $following->name }}</a></li>
            @endforeach
        </ul>
        <h2 class="text-xl font-semibold mt-6">Followers ({{ $user->followers->count() }})</h2>
        <ul class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-2">
            @foreach ($user->followers as $follower)
                <li><a href="{{ route('profile', $follower) }}" class="text-blue-500 hover:underline">{{ $follower->name }}</a></li>
            @endforeach
        </ul>
        <h2 class="text-xl font-semibold mt-6">Pets</h2>
        <ul class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-2">
            @foreach ($user->pets as $pet)
                <li>
                    <a href="{{ route('pet.friends', $pet->id) }}" class="text-blue-500 hover:underline">{{ $pet->name }}</a>
                    @if ($pet->type) ({{ $pet->type }}, {{ $pet->breed }}) @endif
                    - {{ $pet->allFriends()->count() }} friends
                </li>
            @endforeach
        </ul>
    </div>
@endsection
