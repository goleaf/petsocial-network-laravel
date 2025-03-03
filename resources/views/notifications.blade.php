@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white p-6 rounded-lg shadow mb-6">
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold">{{ __('notifications.your_notifications') }}</h1>
            
            <div class="flex space-x-2">
                <a href="{{ route('profile', auth()->user()) }}" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">{{ __('notifications.profile') }}</a>
                <a href="{{ route('posts') }}" class="px-4 py-2 bg-purple-500 text-white rounded-lg hover:bg-purple-600">{{ __('notifications.posts') }}</a>
                <a href="{{ route('messages') }}" class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600">{{ __('notifications.messages') }}</a>
            </div>
        </div>
    </div>
    
    <!-- Notification Center -->
    @livewire('common.notification-center', ['entityType' => 'user', 'entityId' => auth()->id()])
</div>
@endsection
