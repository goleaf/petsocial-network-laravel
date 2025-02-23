@extends('layouts.app')
@section('content')
    <h1>{{ $user->name }}'s Profile</h1>
    <p>{{ $user->profile->bio }}</p>
    @if ($user->profile->avatar)
        <img src="{{ Storage::url($user->profile->avatar) }}" width="100">
    @endif
@endsection
