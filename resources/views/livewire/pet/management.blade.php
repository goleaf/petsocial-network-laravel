<div class="max-w-6xl mx-auto">
    <div class="bg-white p-6 rounded-lg shadow mb-6">
        <h1 class="text-2xl font-bold mb-4 text-center">Manage Your Pets</h1>
        
        @if (session()->has('message'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
                {{ session('message') }}
            </div>
        @endif
        
        <!-- Search and Filter -->
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div class="relative flex-grow">
                <input type="text" wire:model.debounce.300ms="search" class="w-full p-3 pl-10 border rounded-lg" placeholder="Search pets...">
                <x-icons.search class="h-5 w-5 absolute left-3 top-3.5 text-gray-400" />
            </div>
            <div class="flex gap-2">
                <select wire:model="filter" class="p-3 border rounded-lg">
                    <option value="">All Types</option>
                    @foreach ($petTypes as $type)
                        <option value="{{ $type }}">{{ $type }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        
        <!-- Pet Form -->
        <div class="bg-gray-50 p-4 rounded-lg mb-6">
            <h2 class="text-xl font-semibold mb-4">{{ $editMode ? 'Edit Pet' : 'Add New Pet' }}</h2>
            <form wire:submit.prevent="{{ $editMode ? 'update' : 'save' }}" enctype="multipart/form-data">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="mb-3">
                        <label class="block text-gray-700 font-medium mb-1">Name*</label>
                        <input type="text" wire:model="name" class="w-full p-3 border rounded-lg @error('name') border-red-500 @enderror">
                        @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label class="block text-gray-700 font-medium mb-1">Type</label>
                        <input type="text" wire:model="type" class="w-full p-3 border rounded-lg" placeholder="e.g., Dog, Cat, Bird">
                    </div>
                    
                    <div class="mb-3">
                        <label class="block text-gray-700 font-medium mb-1">Breed</label>
                        <input type="text" wire:model="breed" class="w-full p-3 border rounded-lg">
                    </div>
                    
                    <div class="mb-3">
                        <label class="block text-gray-700 font-medium mb-1">Birthdate</label>
                        <input type="date" wire:model="birthdate" class="w-full p-3 border rounded-lg">
                    </div>
                    
                    <div class="mb-3">
                        <label class="block text-gray-700 font-medium mb-1">Location</label>
                        <input type="text" wire:model="location" class="w-full p-3 border rounded-lg" placeholder="e.g., New York, NY">
                    </div>
                    
                    <div class="mb-3">
                        <label class="block text-gray-700 font-medium mb-1">Privacy</label>
                        <select wire:model="is_public" class="w-full p-3 border rounded-lg">
                            <option value="1">Public Profile</option>
                            <option value="0">Private Profile</option>
                        </select>
                    </div>
                    
                    <div class="mb-3 md:col-span-2">
                        <label class="block text-gray-700 font-medium mb-1">Bio</label>
                        <textarea wire:model="bio" class="w-full p-3 border rounded-lg" rows="2" placeholder="Tell us about your pet..."></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="block text-gray-700 font-medium mb-1">Favorite Food</label>
                        <input type="text" wire:model="favorite_food" class="w-full p-3 border rounded-lg">
                    </div>
                    
                    <div class="mb-3">
                        <label class="block text-gray-700 font-medium mb-1">Favorite Toy</label>
                        <input type="text" wire:model="favorite_toy" class="w-full p-3 border rounded-lg">
                    </div>
                    
                    <div class="mb-3 md:col-span-2">
                        <label class="block text-gray-700 font-medium mb-1">Avatar</label>
                        <input type="file" wire:model="avatar" class="w-full p-2 border rounded-lg">
                        @if ($avatar)
                            <div class="mt-2">
                                <img src="{{ $avatar->temporaryUrl() }}" class="h-24 w-24 rounded-full object-cover">
                            </div>
                        @elseif ($editMode && $oldAvatar)
                            <div class="mt-2">
                                <img src="{{ Storage::url($oldAvatar) }}" class="h-24 w-24 rounded-full object-cover">
                            </div>
                        @endif
                    </div>
                </div>
                
                <div class="flex space-x-2 mt-4">
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">
                        {{ $editMode ? 'Update Pet' : 'Add Pet' }}
                    </button>
                    @if ($editMode)
                        <button type="button" wire:click="cancelEdit" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600">
                            Cancel
                        </button>
                    @endif
                </div>
            </form>
        </div>
        
        <!-- Pet List -->
        <h2 class="text-xl font-semibold mb-4">Your Pets</h2>
        @if ($pets->isEmpty())
            <p class="text-gray-500 text-center py-8">No pets added yet. Add your first pet above!</p>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach ($pets as $pet)
                    <div class="border rounded-lg overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                        <div class="p-4">
                            <div class="flex items-center mb-3">
                                @if ($pet->avatar)
                                    <img src="{{ Storage::url($pet->avatar) }}" class="w-16 h-16 rounded-full object-cover mr-3">
                                @else
                                    <div class="w-16 h-16 rounded-full bg-gray-200 flex items-center justify-center mr-3">
                                        <x-icons.heart class="h-8 w-8 text-gray-400" />
                                    </div>
                                @endif
                                <div>
                                    <h3 class="font-bold text-lg">{{ $pet->name }}</h3>
                                    <p class="text-sm text-gray-600">
                                        @if ($pet->type)
                                            {{ $pet->type }}
                                            @if ($pet->breed)
                                                - {{ $pet->breed }}
                                            @endif
                                        @endif
                                    </p>
                                    @if ($pet->location)
                                        <p class="text-xs text-gray-500">📍 {{ $pet->location }}</p>
                                    @endif
                                </div>
                            </div>
                            
                            @if ($pet->birthdate)
                                <p class="text-sm mb-1"><span class="font-medium">Age:</span> {{ $pet->age }}</p>
                            @endif
                            
                            @if ($pet->favorite_food)
                                <p class="text-sm mb-1"><span class="font-medium">Favorite Food:</span> {{ $pet->favorite_food }}</p>
                            @endif
                            
                            @if ($pet->favorite_toy)
                                <p class="text-sm mb-1"><span class="font-medium">Favorite Toy:</span> {{ $pet->favorite_toy }}</p>
                            @endif
                            
                            <div class="flex justify-between mt-4">
                                <div class="flex space-x-2">
                                    <a href="{{ route('pet.profile', $pet->id) }}" class="text-blue-500 hover:underline text-sm">Profile</a>
                                    <a href="{{ route('pet.friends', $pet->id) }}" class="text-blue-500 hover:underline text-sm">Friends</a>
                                    <a href="{{ route('pet.activities', $pet->id) }}" class="text-blue-500 hover:underline text-sm">Activities</a>
                                </div>
                                <div class="flex space-x-2">
                                    <button wire:click="edit({{ $pet->id }})" class="text-gray-500 hover:underline text-sm">Edit</button>
                                    <button wire:click="delete({{ $pet->id }})" class="text-red-500 hover:underline text-sm">Delete</button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="mt-4">
                {{ $pets->links() }}
            </div>
        @endif
    </div>
</div>
