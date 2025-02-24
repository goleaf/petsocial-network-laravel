@extends('layouts.app')
@section('content')
    <div class="max-w-md mx-auto bg-white p-6 rounded-lg shadow">
        <h1 class="text-2xl font-bold mb-4">{{ $user->name }}'s Profile</h1>
        <p>{{ $user->profile->bio }}</p>
        @if ($user->profile->avatar)
            <img src="{{ Storage::url($user->profile->avatar) }}" class="w-24 h-24 rounded-full mt-4">
        @endif
        @if ($user->id !== auth()->id())
            @livewire('friend-button', ['userId' => $user->id], key('friend-'.$user->id))
            @livewire('follow-button', ['userId' => $user->id], key('follow-'.$user->id))
            @livewire('block-button', ['userId' => $user->id], key('block-'.$user->id))
        @endif
        <h2 class="text-xl font-semibold mt-6">Friends ({{ $user->friends->count() }})</h2>
        <ul class="mt-2">
            @foreach ($user->friends as $friend)
                <li><a href="{{ route('profile', $friend) }}" class="text-blue-500 hover:underline">{{ $friend->name }}</a></li>
            @endforeach
        </ul>



        <h2 class="text-xl font-semibold mt-6">Following ({{ $user->following->count() }})</h2>
        <ul class="mt-2">
            @foreach ($user->following as $following)
                <li><a href="{{ route('profile', $following) }}" class="text-blue-500 hover:underline">{{ $following->name }}</a></li>
            @endforeach
        </ul>
        <h2 class="text-xl font-semibold mt-6">Followers ({{ $user->followers->count() }})</h2>
        <ul class="mt-2">
            @foreach ($user->followers as $follower)
                <li><a href="{{ route('profile', $follower) }}" class="text-blue-500 hover:underline">{{ $follower->name }}</a></li>
            @endforeach
        </ul>

        <h2 class="text-xl font-semibold mt-6">Pets</h2>
        <ul class="mt-2">
            @foreach ($user->pets as $pet)
                <li>{{ $pet->name }} @if ($pet->type) ({{ $pet->type }}, {{ $pet->breed }}) @endif</li>
            @endforeach
        </ul>

        @if ($user->id !== auth()->id())
            @php
                $mutualFriends = auth()->user()->friends->intersect($user->friends);
            @endphp
            <h2 class="text-xl font-semibold mt-6">Mutual Friends ({{ $mutualFriends->count() }})</h2>
            <ul class="mt-2">
                @foreach ($mutualFriends as $friend)
                    <li><a href="{{ route('profile', $friend) }}" class="text-blue-500 hover:underline">{{ $friend->name }}</a></li>
                @endforeach
            </ul>
        @endif
    </div>
@endsection
