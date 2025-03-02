<div class="max-w-6xl mx-auto">
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <!-- Pet Header -->
        <div class="relative">
            <div class="h-48 bg-gradient-to-r from-blue-400 to-purple-500"></div>
            <div class="absolute bottom-0 left-0 w-full transform translate-y-1/2 flex justify-center">
                <div class="h-32 w-32 rounded-full border-4 border-white overflow-hidden bg-white">
                    <img src="{{ $pet->avatar_url }}" alt="{{ $pet->name }}" class="h-full w-full object-cover">
                </div>
            </div>
        </div>
        
        <!-- Pet Info -->
        <div class="pt-20 px-6 pb-6">
            <div class="text-center mb-6">
                <h1 class="text-3xl font-bold">{{ $pet->name }}</h1>
                <p class="text-gray-600">
                    @if ($pet->type)
                        {{ $pet->type }}
                        @if ($pet->breed)
                            - {{ $pet->breed }}
                        @endif
                    @endif
                </p>
                @if ($pet->location)
                    <p class="text-gray-500 text-sm mt-1">📍 {{ $pet->location }}</p>
                @endif
            </div>
            
            <!-- Pet Stats -->
            <div class="grid grid-cols-3 gap-4 mb-6 text-center">
                <div class="bg-gray-50 p-3 rounded-lg">
                    <p class="text-2xl font-bold text-blue-500">{{ $friendCount }}</p>
                    <p class="text-gray-600 text-sm">Friends</p>
                </div>
                <div class="bg-gray-50 p-3 rounded-lg">
                    <p class="text-2xl font-bold text-blue-500">{{ $pet->activities->count() }}</p>
                    <p class="text-gray-600 text-sm">Activities</p>
                </div>
                <div class="bg-gray-50 p-3 rounded-lg">
                    <p class="text-2xl font-bold text-blue-500">{{ $pet->age ?? 'N/A' }}</p>
                    <p class="text-gray-600 text-sm">Age</p>
                </div>
            </div>
            
            <!-- Pet Bio -->
            @if ($pet->bio)
                <div class="mb-6">
                    <h2 class="text-xl font-semibold mb-2">About {{ $pet->name }}</h2>
                    <p class="text-gray-700">{{ $pet->bio }}</p>
                </div>
            @endif
            
            <!-- Pet Details -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                @if ($pet->birthdate)
                    <div>
                        <h3 class="font-medium text-gray-700">Birthdate</h3>
                        <p>{{ $pet->birthdate->format('F j, Y') }}</p>
                    </div>
                @endif
                
                @if ($pet->favorite_food)
                    <div>
                        <h3 class="font-medium text-gray-700">Favorite Food</h3>
                        <p>{{ $pet->favorite_food }}</p>
                    </div>
                @endif
                
                @if ($pet->favorite_toy)
                    <div>
                        <h3 class="font-medium text-gray-700">Favorite Toy</h3>
                        <p>{{ $pet->favorite_toy }}</p>
                    </div>
                @endif
                
                <div>
                    <h3 class="font-medium text-gray-700">Owner</h3>
                    <p>{{ $pet->user->name }}</p>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="flex flex-wrap justify-center gap-3 mb-6">
                <a href="{{ route('pet.friends', $pet->id) }}" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    Friends
                </a>
                
                <a href="{{ route('pet.activity', $pet->id) }}" class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    Activities
                </a>
                
                <a href="{{ route('pet.posts', $pet->id) }}" class="px-4 py-2 bg-purple-500 text-white rounded-lg hover:bg-purple-600 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Photos
                </a>
                
                @if (!$isOwner)
                    @livewire('follow.button', ['entityType' => 'pet', 'entityId' => auth()->id(), 'targetId' => $pet->id], key('follow-'.$pet->id))
                    @livewire('common.friend.button', ['entityType' => 'user', 'entityId' => auth()->id(), 'targetId' => $pet->id, 'targetType' => 'pet'], key('friend-'.$pet->id))
                @endif
                
                @if ($isOwner)
                    <a href="{{ route('pet.edit', $pet->id) }}" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Edit Profile
                    </a>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Friends Section -->
    <div class="mt-6 bg-white p-6 rounded-lg shadow">
        <h2 class="text-2xl font-bold mb-4">{{ $pet->name }}'s Friends</h2>
        @livewire('common.friend.list', ['entityType' => 'pet', 'entityId' => $pet->id, 'limit' => 8])
        <div class="mt-4 text-center">
            <a href="{{ route('pet.friends', $pet->id) }}" class="text-blue-500 hover:underline">View All Friends</a>
        </div>
    </div>
    
    <!-- Activities Section -->
    <div class="mt-6 bg-white p-6 rounded-lg shadow">
        <h2 class="text-2xl font-bold mb-4">Recent Activities</h2>
        @livewire('common.friend.activity-log', ['entityType' => 'pet', 'entityId' => $pet->id, 'limit' => 5])
        <div class="mt-4 text-center">
            <a href="{{ route('pet.activity', $pet->id) }}" class="text-blue-500 hover:underline">View All Activities</a>
        </div>
    </div>
    
    <!-- Friend Suggestions -->
    <div class="mt-6 bg-white p-6 rounded-lg shadow">
        <h2 class="text-2xl font-bold mb-4">Suggested Friends</h2>
        @livewire('common.friend.suggestions', ['entityType' => 'pet', 'entityId' => $pet->id, 'limit' => 4])
        <div class="mt-4 text-center">
            <a href="{{ route('pet.finder', $pet->id) }}" class="text-blue-500 hover:underline">Find More Friends</a>
        </div>
    </div>
    
    <!-- Photos Section -->
    <div class="mt-6 bg-white p-6 rounded-lg shadow">
        <h2 class="text-2xl font-bold mb-4">Photos</h2>
        @php
            $photos = $pet->activities()->whereNotNull('image')->latest('happened_at')->limit(12)->get();
        @endphp
        
        @if ($photos->isNotEmpty())
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                @foreach ($photos as $photo)
                    <a href="{{ $photo->image_url }}" target="_blank" class="block">
                        <img src="{{ $photo->image_url }}" alt="{{ $photo->activity_type_name }}" class="w-full h-32 object-cover rounded-lg hover:opacity-90 transition-opacity">
                    </a>
                @endforeach
            </div>
            <div class="mt-4 text-center">
                <a href="{{ route('pet.posts', $pet->id) }}" class="text-blue-500 hover:underline">View All Photos</a>
            </div>
        @else
            <p class="text-gray-500 text-center py-4">No photos uploaded yet.</p>
            @if ($isOwner)
                <div class="text-center mt-2">
                    <a href="{{ route('pet.posts', $pet->id) }}" class="text-blue-500 hover:underline">Upload Photos</a>
                </div>
            @endif
        @endif
    </div>
</div>
