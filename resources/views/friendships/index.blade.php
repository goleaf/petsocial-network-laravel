@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="px-4 py-6 sm:px-0">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <h1 class="text-2xl font-bold mb-6">{{ $entityType === 'user' ? 'User' : 'Pet' }} Friendships</h1>
                
                @if (session('success'))
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
                        {{ session('success') }}
                    </div>
                @endif
                
                @if (session('error'))
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
                        {{ session('error') }}
                    </div>
                @endif
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Friends List -->
                    <div class="md:col-span-2">
                        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                            <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">Friends</h3>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                    {{ $friends->total() }} {{ Str::plural('friend', $friends->total()) }}
                                </span>
                            </div>
                            <div class="border-t border-gray-200">
                                @if ($friends->isEmpty())
                                    <div class="px-4 py-5 sm:p-6 text-center text-gray-500">
                                        No friends yet. Send some friend requests!
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
                                                                {{ $friend->email }}
                                                            @else
                                                                {{ $friend->breed }}
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="flex space-x-2">
                                                    @if ($entityType === 'user')
                                                        <a href="{{ route('profile', $friend) }}" class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-4 font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                                            View Profile
                                                        </a>
                                                    @else
                                                        <a href="{{ route('pet.profile', $friend) }}" class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-4 font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                                            View Profile
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
                                <h3 class="text-lg leading-6 font-medium text-gray-900">Pending Requests</h3>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                    {{ count($pendingRequests) }}
                                </span>
                            </div>
                            <div class="border-t border-gray-200">
                                @if (empty($pendingRequests))
                                    <div class="px-4 py-5 sm:p-6 text-center text-gray-500">
                                        No pending friend requests.
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
                                                                {{ $request->sender->name ?? 'Unknown' }}
                                                            </div>
                                                            <div class="text-xs text-gray-500">
                                                                {{ $request->created_at->diffForHumans() }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="flex space-x-2">
                                                        @livewire('common.friend.button', [
                                                            'entityType' => $entityType, 
                                                            'entityId' => $entity->id, 
                                                            'targetId' => $request->sender_id
                                                        ], key('pending-'.$request->id))
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
                                <h3 class="text-lg leading-6 font-medium text-gray-900">Sent Requests</h3>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                    {{ count($sentRequests) }}
                                </span>
                            </div>
                            <div class="border-t border-gray-200">
                                @if (empty($sentRequests))
                                    <div class="px-4 py-5 sm:p-6 text-center text-gray-500">
                                        No sent friend requests.
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
                                                                {{ $request->recipient->name ?? 'Unknown' }}
                                                            </div>
                                                            <div class="text-xs text-gray-500">
                                                                {{ $request->created_at->diffForHumans() }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <form action="{{ route('friendships.remove', $request->recipient_id) }}" method="POST" class="inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="inline-flex items-center px-2 py-1 border border-transparent text-xs leading-4 font-medium rounded-md text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                                                Cancel Request
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
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
