<div class="bg-white shadow rounded-lg p-4 space-y-6">
    <!-- Create Post Form -->
    @if(auth()->check() && ($entityType === 'user' && $entityId === auth()->id()))
    <div class="space-y-4">
        <h2 class="text-xl font-semibold">Create Post</h2>
        
        <form wire:submit.prevent="save" class="space-y-4">
            <div>
                <textarea 
                    wire:model.defer="content" 
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" 
                    rows="3" 
                    placeholder="What's on your mind?"
                ></textarea>
                @error('content') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
            
            <div class="flex flex-wrap gap-4">
                <div class="flex-1">
                    <input 
                        wire:model.defer="tags" 
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" 
                        placeholder="Tags (comma separated)"
                    >
                    @error('tags') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                
                @if(count($userPets) > 0)
                <div class="w-48">
                    <select 
                        wire:model.defer="pet_id" 
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                    >
                        <option value="">Post as yourself</option>
                        @foreach($userPets as $pet)
                        <option value="{{ $pet->id }}">Post as {{ $pet->name }}</option>
                        @endforeach
                    </select>
                    @error('pet_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                @endif
            </div>
            
            <div class="flex items-center space-x-4">
                <div class="flex-1">
                    <label for="photo" class="flex items-center space-x-2 cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span>Add Photo</span>
                        <input id="photo" type="file" wire:model="photo" class="hidden">
                    </label>
                    @error('photo') <span class="text-red-500 text-sm block mt-1">{{ $message }}</span> @enderror
                    
                    @if($photo)
                    <div class="mt-2">
                        <img src="{{ $photo->temporaryUrl() }}" class="h-20 w-auto rounded">
                    </div>
                    @endif
                </div>
                
                <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition">
                    Post
                </button>
            </div>
        </form>
    </div>
    
    <hr class="border-gray-200">
    @endif
    
    <!-- Post Filters -->
    <div class="flex flex-wrap justify-between items-center">
        <h2 class="text-xl font-semibold">
            @if($entityType === 'user')
                @if($entityId === auth()->id())
                    Your Posts
                @else
                    {{ $entity->name }}'s Posts
                @endif
            @else
                {{ $entity->name }}'s Posts
            @endif
        </h2>
        
        <div class="flex space-x-2">
            <input 
                wire:model.debounce.300ms="searchTerm" 
                type="text" 
                placeholder="Search posts..." 
                class="rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
            >
            
            <select 
                wire:model="filter" 
                class="rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
            >
                <option value="all">All Posts</option>
                <option value="user">User Posts</option>
                @if($entityType === 'user')
                <option value="pets">Pet Posts</option>
                @if($entityId === auth()->id())
                <option value="friends">Friend Posts</option>
                @endif
                @endif
            </select>
        </div>
    </div>
    
    <!-- Posts List -->
    <div class="space-y-6">
        @if(session()->has('message'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('message') }}</span>
        </div>
        @endif
        
        @forelse($posts as $post)
        <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
            <!-- Post Header -->
            <div class="flex items-start space-x-3">
                @if($post->pet_id)
                <img src="{{ $post->pet->profile_photo_url }}" alt="{{ $post->pet->name }}" class="h-10 w-10 rounded-full">
                @else
                <img src="{{ $post->user->profile_photo_url }}" alt="{{ $post->user->name }}" class="h-10 w-10 rounded-full">
                @endif
                
                <div class="flex-1">
                    <div class="flex justify-between">
                        <div>
                            <p class="font-medium">
                                @if($post->pet_id)
                                {{ $post->pet->name }} 
                                <span class="text-gray-500 text-sm">({{ $post->user->name }}'s pet)</span>
                                @else
                                {{ $post->user->name }}
                                @endif
                            </p>
                            <p class="text-gray-500 text-sm">{{ $post->created_at->diffForHumans() }}</p>
                        </div>
                        
                        @if($post->user_id === auth()->id())
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="text-gray-500 hover:text-gray-700">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
                                </svg>
                            </button>
                            
                            <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10">
                                <button wire:click="edit({{ $post->id }})" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    Edit
                                </button>
                                <button wire:click="delete({{ $post->id }})" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                                    Delete
                                </button>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- Post Content -->
            <div class="mt-3">
                <p class="text-gray-800 whitespace-pre-line">{{ $post->content }}</p>
                
                @if($post->photo)
                <div class="mt-3">
                    <img src="{{ Storage::url($post->photo) }}" alt="Post image" class="rounded-lg max-h-96 w-auto">
                </div>
                @endif
                
                @if($post->tags->count() > 0)
                <div class="mt-2 flex flex-wrap gap-1">
                    @foreach($post->tags as $tag)
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        #{{ $tag->name }}
                    </span>
                    @endforeach
                </div>
                @endif
            </div>
            
            <!-- Post Stats -->
            <div class="mt-3 flex items-center text-gray-500 text-sm space-x-4">
                <span>{{ $post->likes->count() }} likes</span>
                <span>{{ $post->comments->count() }} comments</span>
            </div>
            
            <!-- Post Actions -->
            <div class="mt-3 flex border-t border-b border-gray-200 py-2">
                <button class="flex-1 flex justify-center items-center space-x-2 text-gray-500 hover:text-blue-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5" />
                    </svg>
                    <span>Like</span>
                </button>
                
                <button class="flex-1 flex justify-center items-center space-x-2 text-gray-500 hover:text-blue-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                    <span>Comment</span>
                </button>
                
                <button class="flex-1 flex justify-center items-center space-x-2 text-gray-500 hover:text-blue-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                    </svg>
                    <span>Share</span>
                </button>
            </div>
            
            <!-- Comments Section -->
            <div class="mt-3">
                @livewire('common.comment-manager', ['postId' => $post->id], key('post-'.$post->id))
            </div>
        </div>
        @empty
        <div class="text-center py-8 text-gray-500">
            @if(!empty($searchTerm))
                No posts found matching "{{ $searchTerm }}".
            @else
                No posts found.
            @endif
        </div>
        @endforelse
        
        <!-- Pagination -->
        <div>
            {{ $posts->links() }}
        </div>
    </div>
    
    <!-- Edit Post Modal -->
    @if($editingPostId)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-lg">
            <h3 class="text-lg font-medium mb-4">Edit Post</h3>
            
            <form wire:submit.prevent="update" class="space-y-4">
                <div>
                    <textarea 
                        wire:model.defer="editingContent" 
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" 
                        rows="4"
                    ></textarea>
                    @error('editingContent') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                
                <div>
                    <input 
                        wire:model.defer="editingTags" 
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" 
                        placeholder="Tags (comma separated)"
                    >
                    @error('editingTags') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                
                <div class="flex justify-end space-x-2">
                    <button type="button" wire:click="$set('editingPostId', null)" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 transition">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition">
                        Update
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
