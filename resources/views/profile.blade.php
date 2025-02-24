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
            @livewire('block-button', ['userId' => $user->id], key('block-'.$user->id))
        @endif
        <h2 class="text-xl font-semibold mt-6">Friends ({{ $user->friends->count() }})</h2>
        <ul class="mt-2">
            @foreach ($user->friends as $friend)
                <li><a href="{{ route('profile', $friend) }}" class="text-blue-500 hover:underline">{{ $friend->name }}</a></li>
            @endforeach
        </ul>
    </div>
@endsection
