@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="px-4 py-6 sm:px-0">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <h1 class="text-2xl font-bold mb-6">{{ $entityType === 'user' ? __('friendships.user_friendships') : __('friendships.pet_friendships') }}</h1>
                
                @if (session('success'))
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
                        {{ __('friendships.success_message') }}
                    </div>
                @endif
                
                @if (session('error'))
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
                        {{ __('friendships.error_message') }}
                    </div>
                @endif
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Friends List -->
                    <div class="md:col-span-2">
                        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                            <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">{{ __('friendships.my_friends') }}</h3>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                    {{ trans_choice('friendships.friends_count', $friends->total()) }}
                                </span>
                            </div>
                            <div class="border-t border-gray-200">
                                @if ($friends->isEmpty())
                                    <div class="px-4 py-5 sm:p-6 text-center text-gray-500">
                                        {{ __('friendships.no_friends') }}
                                    </div>
                                @else
                                    <ul class="divide-y divide-gray-200">
                                        @foreach ($friends as $friend)
                                            <li class="px-4 py-4 flex items-center justify-between">
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 h-10 w-10">
                                                        @if ($entityType === 'user' && $friend->profile->avatar)
                                                            <img class="h-10 w-10 rounded-full" src="{{ Storage::url($friend->profile->avatar) }}" alt="{{ $friend->name }}">
                                                        @elseif ($entityType === 'pet' && $friend->avatar)
                                                            <img class="h-10 w-10 rounded-full" src="{{ Storage::url($friend->avatar) }}" alt="{{ $friend->name }}">
                                                        @else
                                                            <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center">
                                                                <span class="text-gray-500">{{ substr($friend->name, 0, 1) }}</span>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div class="ml-4">
                                                        <div class="text-sm font-medium text-gray-900">{{ $friend->name }}</div>
                                                        <div class="text-sm text-gray-500">
                                                            @if ($entityType === 'user')
                                                                {{ __('friendships.email') }}: {{ $friend->email }}
                                                            @else
                                                                {{ __('friendships.breed') }}: {{ $friend->breed }}
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="flex space-x-2">
                                                    @if ($entityType === 'user')
                                                        <a href="{{ route('profile', $friend) }}" class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-4 font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                                            <x-icons.user class="h-4 w-4 mr-1" stroke-width="2" /> {{ __('friendships.view_profile') }}
                                                        </a>
                                                    @else
                                                        <a href="{{ route('pet.profile', $friend) }}" class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-4 font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                                            <x-icons.user class="h-4 w-4 mr-1" stroke-width="2" /> {{ __('friendships.view_profile') }}
                                                        </a>
                                                    @endif
                                                    
                                                    @livewire('common.friend.button', [
                                                        'entityType' => $entityType, 
                                                        'entityId' => $entity->id, 
                                                        'targetId' => $friend->id
                                                    ], key('friend-'.$friend->id))
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                    
                                    <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
                                        {{ $friends->links() }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <!-- Sidebar -->
                    <div>
                        <!-- Pending Requests -->
                        <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
                            <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">{{ __('friendships.pending_requests') }}</h3>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                    {{ count($pendingRequests) }}
                                </span>
                            </div>
                            <div class="border-t border-gray-200">
                                @if (empty($pendingRequests))
                                    <div class="px-4 py-5 sm:p-6 text-center text-gray-500">
                                        {{ __('friendships.no_pending') }}
                                    </div>
                                @else
                                    <ul class="divide-y divide-gray-200">
                                        @foreach ($pendingRequests as $request)
                                            <li class="px-4 py-4">
                                                <div class="flex items-center justify-between">
                                                    <div class="flex items-center">
                                                        <div class="flex-shrink-0 h-8 w-8">
                                                            @if ($entityType === 'user' && isset($request->sender->profile->avatar))
                                                                <img class="h-8 w-8 rounded-full" src="{{ Storage::url($request->sender->profile->avatar) }}" alt="{{ $request->sender->name }}">
                                                            @elseif ($entityType === 'pet' && isset($request->sender->avatar))
                                                                <img class="h-8 w-8 rounded-full" src="{{ Storage::url($request->sender->avatar) }}" alt="{{ $request->sender->name }}">
                                                            @else
                                                                <div class="h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center">
                                                                    <span class="text-gray-500">{{ substr($request->sender->name ?? 'U', 0, 1) }}</span>
                                                                </div>
                                                            @endif
                                                        </div>
                                                        <div class="ml-3">
                                                            <div class="text-sm font-medium text-gray-900">
                                                                {{ $request->sender->name ?? __('friendships.unknown') }}
                                                            </div>
                                                            <div class="text-xs text-gray-500">
                                                                {{ __('friendships.sent_at') }} {{ $request->created_at->diffForHumans() }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="flex space-x-2">
                                                        <form action="{{ route('friendship.accept') }}" method="POST">
                                                            @csrf
                                                            <input type="hidden" name="entity_type" value="{{ $entityType }}">
                                                            <input type="hidden" name="entity_id" value="{{ $entity->id }}">
                                                            <input type="hidden" name="friend_id" value="{{ $request->sender_id }}">
                                                            <button type="submit" class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                                                <x-icons.check class="h-3 w-3 mr-1" stroke-width="2" /> {{ __('friendships.accept_request') }}
                                                            </button>
                                                        </form>
                                                        <form action="{{ route('friendship.decline') }}" method="POST">
                                                            @csrf
                                                            <input type="hidden" name="entity_type" value="{{ $entityType }}">
                                                            <input type="hidden" name="entity_id" value="{{ $entity->id }}">
                                                            <input type="hidden" name="friend_id" value="{{ $request->sender_id }}">
                                                            <button type="submit" class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                                                <x-icons.x class="h-3 w-3 mr-1" stroke-width="2" /> {{ __('friendships.reject_request') }}
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        </div>
                        
                        <!-- Sent Requests -->
                        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                            <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">{{ __('friendships.sent_requests') }}</h3>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                    {{ count($sentRequests) }}
                                </span>
                            </div>
                            <div class="border-t border-gray-200">
                                @if (empty($sentRequests))
                                    <div class="px-4 py-5 sm:p-6 text-center text-gray-500">
                                        {{ __('friendships.no_sent_requests') }}
                                    </div>
                                @else
                                    <ul class="divide-y divide-gray-200">
                                        @foreach ($sentRequests as $request)
                                            <li class="px-4 py-4">
                                                <div class="flex items-center justify-between">
                                                    <div class="flex items-center">
                                                        <div class="flex-shrink-0 h-8 w-8">
                                                            @if ($entityType === 'user' && isset($request->recipient->profile->avatar))
                                                                <img class="h-8 w-8 rounded-full" src="{{ Storage::url($request->recipient->profile->avatar) }}" alt="{{ $request->recipient->name }}">
                                                            @elseif ($entityType === 'pet' && isset($request->recipient->avatar))
                                                                <img class="h-8 w-8 rounded-full" src="{{ Storage::url($request->recipient->avatar) }}" alt="{{ $request->recipient->name }}">
                                                            @else
                                                                <div class="h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center">
                                                                    <span class="text-gray-500">{{ substr($request->recipient->name ?? 'U', 0, 1) }}</span>
                                                                </div>
                                                            @endif
                                                        </div>
                                                        <div class="ml-3">
                                                            <div class="text-sm font-medium text-gray-900">
                                                                {{ $request->recipient->name ?? __('friendships.unknown') }}
                                                            </div>
                                                            <div class="text-xs text-gray-500">
                                                                {{ __('friendships.sent_at') }} {{ $request->created_at->diffForHumans() }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <form action="{{ route('friendship.cancel') }}" method="POST">
                                                        @csrf
                                                        <input type="hidden" name="entity_type" value="{{ $entityType }}">
                                                        <input type="hidden" name="entity_id" value="{{ $entity->id }}">
                                                        <input type="hidden" name="friend_id" value="{{ $request->recipient_id }}">
                                                        <button type="submit" class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-white bg-gray-600 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                                                            <x-icons.x class="h-3 w-3 mr-1" stroke-width="2" /> {{ __('friendships.cancel_request') }}
                                                        </button>
                                                    </form>
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
