<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <div class="bg-white shadow-sm rounded-lg overflow-hidden p-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Groups</h1>
            <button wire:click="$set('showCreateModal', true)" class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-blue-700">
                Create Group
            </button>
        </div>
        
        <div class="mb-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="relative flex-grow max-w-md">
                    <input type="text" wire:model.debounce.300ms="search" placeholder="Search groups..." class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        @svg('heroicon-o-search', 'h-5 w-5 text-gray-400')
                    </div>
                </div>
                
                <div class="flex space-x-2">
                    <button wire:click="$set('filter', 'all')" class="px-3 py-2 text-sm font-medium rounded-md {{ $filter === 'all' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        All Groups
                    </button>
                    <button wire:click="$set('filter', 'my')" class="px-3 py-2 text-sm font-medium rounded-md {{ $filter === 'my' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        My Groups
                    </button>
                    <button wire:click="$set('filter', 'open')" class="px-3 py-2 text-sm font-medium rounded-md {{ $filter === 'open' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        Open
                    </button>
                    <button wire:click="$set('filter', 'closed')" class="px-3 py-2 text-sm font-medium rounded-md {{ $filter === 'closed' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        Closed
                    </button>
                </div>
            </div>
        </div>
        
        @if (session()->has('message'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6">
                {{ session('message') }}
            </div>
        @endif
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse ($groups as $group)
                <div class="bg-white border border-gray-200 rounded-lg overflow-hidden shadow-sm hover:shadow-md transition-shadow duration-200">
                    <div class="h-32 bg-cover bg-center" style="background-image: url('{{ $group->cover_image ? Storage::url($group->cover_image) : 'https://via.placeholder.com/800x300?text=Group+Cover' }}')"></div>
                    
                    <div class="p-4">
                        <div class="flex items-center mb-3">
                            @if ($group->icon)
                                <img src="{{ Storage::url($group->icon) }}" alt="{{ $group->name }}" class="h-10 w-10 rounded-full mr-3">
                            @else
                                <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center mr-3">
                                    @text('{{ substr($group->name, 0, 1) }}')
                                </div>
                            @endif
                            
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">{{ $group->name }}</h3>
                                <p class="text-sm text-gray-500">{{ $group->members_count }} {{ Str::plural('member', $group->members_count) }}</p>
                            </div>
                        </div>
                        
                        <p class="text-gray-700 text-sm mb-4 line-clamp-2">{{ $group->description }}</p>
                        
                        <div class="flex items-center justify-between">
                            @badge([
                                'color' => $group->visibility === 'open' ? 'green' : ($group->visibility === 'closed' ? 'yellow' : 'red'),
                                'text' => ucfirst($group->visibility)
                            ])
                            
                            <div>
                                @if ($group->members->contains(auth()->id()))
                                    <button wire:click="leaveGroup({{ $group->id }})" class="text-sm text-red-600 hover:text-red-800">
                                        Leave
                                    </button>
                                @else
                                    <button wire:click="joinGroup({{ $group->id }})" class="text-sm font-medium text-blue-600 hover:text-blue-800">
                                        {{ $group->visibility === 'open' ? 'Join' : 'Request to Join' }}
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-gray-50 px-4 py-3 border-t border-gray-200">
                        <a href="{{ route('group.detail', $group) }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                            View Group
                        </a>
                    </div>
                </div>
            @empty
                <div class="col-span-3 py-12 text-center">
                    @svg('heroicon-o-user-group', 'mx-auto h-12 w-12 text-gray-400')
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No groups found</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Get started by creating a new group or adjusting your search filters.
                    </p>
                    <div class="mt-6">
                        <button wire:click="$set('showCreateModal', true)" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            @svg('heroicon-o-plus', '-ml-1 mr-2 h-5 w-5')
                            Create Group
                        </button>
                    </div>
                </div>
            @endforelse
        </div>
        
        <div class="mt-6">
            {{ $groups->links() }}
        </div>
    </div>
    
    <!-- Create Group Modal -->
    @if ($showCreateModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-75 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-screen overflow-y-auto">
                <div class="p-6">
                    <h2 class="text-xl font-bold mb-4">Create New Group</h2>
                    
                    <form wire:submit.prevent="createGroup">
                        <div class="space-y-4">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">Group Name</label>
                                <input type="text" id="name" wire:model="name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                @error('name') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                            </div>
                            
                            <div>
                                <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                                <textarea id="description" wire:model="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                                @error('description') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                            </div>
                            
                            <div>
                                <label for="category" class="block text-sm font-medium text-gray-700">Category</label>
                                <select id="category" wire:model="category" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Select a category</option>
                                    <option value="Dogs">Dogs</option>
                                    <option value="Cats">Cats</option>
                                    <option value="Birds">Birds</option>
                                    <option value="Fish">Fish</option>
                                    <option value="Reptiles">Reptiles</option>
                                    <option value="Small Pets">Small Pets</option>
                                    <option value="Other">Other</option>
                                </select>
                                @error('category') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                            </div>
                            
                            <div>
                                <label for="visibility" class="block text-sm font-medium text-gray-700">Visibility</label>
                                <select id="visibility" wire:model="visibility" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="open">Open (Anyone can join)</option>
                                    <option value="closed">Closed (Request to join)</option>
                                    <option value="private">Private (Invitation only)</option>
                                </select>
                                @error('visibility') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                            </div>
                            
                            <div>
                                <label for="location" class="block text-sm font-medium text-gray-700">Location (Optional)</label>
                                <input type="text" id="location" wire:model="location" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                @error('location') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                            </div>
                            
                            <div>
                                <label for="coverImage" class="block text-sm font-medium text-gray-700">Cover Image</label>
                                <input type="file" id="coverImage" wire:model="coverImage" class="mt-1 block w-full">
                                @error('coverImage') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                            </div>
                            
                            <div>
                                <label for="icon" class="block text-sm font-medium text-gray-700">Group Icon</label>
                                <input type="file" id="icon" wire:model="icon" class="mt-1 block w-full">
                                @error('icon') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        
                        <div class="mt-6 flex justify-end space-x-3">
                            <button type="button" wire:click="$set('showCreateModal', false)" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                                Cancel
                            </button>
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                Create Group
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
