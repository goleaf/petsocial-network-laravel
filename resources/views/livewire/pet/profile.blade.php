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
                    <p class="text-gray-600 text-sm">{{ __('profile.friends') }}</p>
                </div>
                <div class="bg-gray-50 p-3 rounded-lg">
                    <p class="text-2xl font-bold text-blue-500">{{ $pet->activities->count() }}</p>
                    <p class="text-gray-600 text-sm">{{ __('profile.activities') }}</p>
                </div>
                <div class="bg-gray-50 p-3 rounded-lg">
                    <p class="text-2xl font-bold text-blue-500">{{ $pet->age ?? 'N/A' }}</p>
                    <p class="text-gray-600 text-sm">{{ __('profile.age') }}</p>
                </div>
            </div>
            
            <!-- Pet Bio -->
            @if ($pet->bio)
                <div class="mb-6">
                    <h2 class="text-xl font-semibold mb-2">{{ __('profile.about') }} {{ $pet->name }}</h2>
                    <p class="text-gray-700">{{ $pet->bio }}</p>
                </div>
            @endif
            
            <!-- Pet Details -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                @if ($pet->birthdate)
                    <div>
                        <h3 class="font-medium text-gray-700">{{ __('profile.birthdate') }}</h3>
                        <p>{{ $pet->birthdate->format('F j, Y') }}</p>
                    </div>
                @endif
                
                @if ($pet->favorite_food)
                    <div>
                        <h3 class="font-medium text-gray-700">{{ __('profile.favorite_food') }}</h3>
                        <p>{{ $pet->favorite_food }}</p>
                    </div>
                @endif
                
                @if ($pet->favorite_toy)
                    <div>
                        <h3 class="font-medium text-gray-700">{{ __('profile.favorite_toy') }}</h3>
                        <p>{{ $pet->favorite_toy }}</p>
                    </div>
                @endif
                
                <div>
                    <h3 class="font-medium text-gray-700">{{ __('profile.owner') }}</h3>
                    <p>{{ $pet->user->name }}</p>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="flex flex-wrap justify-center gap-3 mb-6">
                <a href="{{ route('pet.friends', $pet->id) }}" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 flex items-center">
                    <x-icons.friends class="h-5 w-5 mr-1" />
                    {{ __('profile.friends') }}
                </a>
                
                <a href="{{ route('activity', ['entity_type' => 'pet', 'entity_id' => $pet->id]) }}" class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 flex items-center">
                    <x-icons.activity class="h-5 w-5 mr-1" />
                    {{ __('profile.activities') }}
                </a>
                
                <a href="{{ route('pet.posts', $pet->id) }}" class="px-4 py-2 bg-purple-500 text-white rounded-lg hover:bg-purple-600 flex items-center">
                    <x-icons.photos class="h-5 w-5 mr-1" />
                    {{ __('profile.photos') }}
                </a>
                
                @if (!$isOwner)
                    @livewire('common.follow.button', ['entityType' => 'pet', 'entityId' => auth()->id(), 'targetId' => $pet->id], key('follow-'.$pet->id))
                    @livewire('common.friend.button', ['entityType' => 'user', 'entityId' => auth()->id(), 'targetId' => $pet->id, 'targetType' => 'pet'], key('friend-'.$pet->id))
                @endif
                
                @if ($isOwner)
                    <a href="{{ route('pet.edit', $pet->id) }}" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 flex items-center">
                        <x-icons.edit class="h-5 w-5 mr-1" />
                        {{ __('profile.edit_profile') }}
                    </a>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Friends Section -->
    <div class="mt-6 bg-white p-6 rounded-lg shadow">
        <h2 class="text-2xl font-bold mb-4">{{ __('profile.pet_friends', ['name' => $pet->name]) }}</h2>
        @livewire('common.friend.list', ['entityType' => 'pet', 'entityId' => $pet->id, 'limit' => 8])
        <div class="mt-4 text-center">
            <a href="{{ route('pet.friends', $pet->id) }}" class="text-blue-500 hover:underline">{{ __('profile.view_all_friends') }}</a>
        </div>
    </div>
    
    <!-- Activities Section -->
    <div class="mt-6 bg-white p-6 rounded-lg shadow">
        <h2 class="text-2xl font-bold mb-4">{{ __('profile.recent_activities') }}</h2>
        @livewire('common.friend.activity-log', ['entityType' => 'pet', 'entityId' => $pet->id, 'limit' => 5])
        <div class="mt-4 text-center">
            <a href="{{ route('activity', ['entity_type' => 'pet', 'entity_id' => $pet->id]) }}" class="inline-flex items-center px-4 py-2 bg-green-100 text-green-700 rounded-full hover:bg-green-200 transition-colors duration-200">
                <x-icons.arrow-right class="h-4 w-4 mr-1" />
                {{ __('profile.view_all_activities') }}
            </a>
        </div>
    </div>
    
    <!-- Friend Suggestions -->
    <div class="mt-6 bg-white p-6 rounded-lg shadow">
        <h2 class="text-2xl font-bold mb-4">{{ __('profile.suggested_friends') }}</h2>
        @livewire('common.friend.suggestions', ['entityType' => 'pet', 'entityId' => $pet->id, 'limit' => 4])
        <div class="mt-4 text-center">
            <a href="{{ route('pet.finder', $pet->id) }}" class="inline-flex items-center px-4 py-2 bg-blue-100 text-blue-700 rounded-full hover:bg-blue-200 transition-colors duration-200">
                <x-icons.search class="h-4 w-4 mr-1" />
                {{ __('profile.find_more_friends') }}
            </a>
        </div>
    </div>
    
    <!-- Photos Section -->
    <div class="mt-6 bg-white p-6 rounded-lg shadow">
        <h2 class="text-2xl font-bold mb-4">{{ __('profile.photos') }}</h2>
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
                <a href="{{ route('pet.posts', $pet->id) }}" class="text-blue-500 hover:underline">{{ __('profile.view_all_photos') }}</a>
            </div>
        @else
            <p class="text-gray-500 text-center py-4">{{ __('profile.no_photos') }}</p>
            @if ($isOwner)
                <div class="text-center mt-2">
                    <a href="{{ route('pet.posts', $pet->id) }}" class="text-blue-500 hover:underline">{{ __('profile.upload_photos') }}</a>
                </div>
            @endif
        @endif
    </div>
</div>
