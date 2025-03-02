<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <!-- Search Header -->
        <div class="p-6 border-b border-gray-200">
            <div class="flex flex-col space-y-4 md:flex-row md:items-center md:space-y-0 md:space-x-4">
                <div class="flex-1">
                    <div class="relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <input 
                            wire:model.debounce.300ms="query" 
                            type="text" 
                            class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 pr-12 py-3 sm:text-sm border-gray-300 rounded-md" 
                            placeholder="Search for posts, users, pets, or tags..."
                            autofocus
                        >
                    </div>
                </div>
                
                <div class="flex space-x-2">
                    <select 
                        wire:model="type" 
                        class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                    >
                        <option value="all">All Types</option>
                        <option value="posts">Posts</option>
                        <option value="users">Users</option>
                        <option value="pets">Pets</option>
                        <option value="tags">Tags</option>
                    </select>
                    
                    <select 
                        wire:model="filter" 
                        class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                    >
                        <option value="all">All</option>
                        <option value="friends">Friends Only</option>
                        <option value="following">Following Only</option>
                    </select>
                    
                    <select 
                        wire:model="sortField" 
                        class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                    >
                        <option value="created_at">Newest</option>
                        <option value="name">Name</option>
                        <option value="popularity">Popularity</option>
                    </select>
                    
                    <button 
                        wire:click="$set('sortDirection', '{{ $sortDirection === 'asc' ? 'desc' : 'asc' }}')" 
                        class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    >
                        @if($sortDirection === 'asc')
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12" />
                        </svg>
                        @else
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h9m5-4v12m0 0l-4-4m4 4l4-4" />
                        </svg>
                        @endif
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Search Results -->
        <div class="divide-y divide-gray-200">
            @if(empty($query))
                <div class="p-10 text-center text-gray-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <p class="mt-4 text-lg">Enter a search term to find posts, users, pets, or tags</p>
                </div>
            @elseif($results['total'] === 0)
                <div class="p-10 text-center text-gray-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="mt-4 text-lg">No results found for "{{ $query }}"</p>
                    <p class="mt-2 text-sm">Try adjusting your search or filter to find what you're looking for</p>
                </div>
            @else
                <!-- Posts Results -->
                @if(($type === 'all' || $type === 'posts') && $results['posts']->count() > 0)
                    <div class="p-6">
                        <h2 class="text-xl font-semibold mb-4">Posts ({{ $results['posts']->total() }})</h2>
                        <div class="space-y-6">
                            @foreach($results['posts'] as $post)
                                <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
                                    <!-- Post Header -->
                                    <div class="flex items-start space-x-3">
                                        @if($post->pet_id)
                                            <img src="{{ $post->pet->profile_photo_url }}" alt="{{ $post->pet->name }}" class="h-10 w-10 rounded-full">
                                        @else
                                            <img src="{{ $post->user->profile_photo_url }}" alt="{{ $post->user->name }}" class="h-10 w-10 rounded-full">
                                        @endif
                                        
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
                                    </div>
                                    
                                    <!-- Post Content -->
                                    <div class="mt-3">
                                        <p class="text-gray-800">{{ $post->content }}</p>
                                        
                                        @if($post->photo)
                                            <div class="mt-3">
                                                <img src="{{ Storage::url($post->photo) }}" alt="Post image" class="rounded-lg max-h-64 w-auto">
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
                                </div>
                            @endforeach
                            
                            @if($type === 'posts')
                                <div>
                                    {{ $results['posts']->links() }}
                                </div>
                            @elseif($results['posts']->hasMorePages())
                                <div class="text-center">
                                    <button wire:click="$set('type', 'posts')" class="text-indigo-600 hover:text-indigo-900">
                                        View all posts
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
                
                <!-- Users Results -->
                @if(($type === 'all' || $type === 'users') && $results['users']->count() > 0)
                    <div class="p-6">
                        <h2 class="text-xl font-semibold mb-4">Users ({{ $results['users']->total() }})</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($results['users'] as $user)
                                <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
                                    <div class="flex items-center space-x-4">
                                        <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}" class="h-16 w-16 rounded-full">
                                        
                                        <div>
                                            <h3 class="text-lg font-medium">{{ $user->name }}</h3>
                                            <p class="text-gray-500 text-sm">{{ $user->email }}</p>
                                            <div class="flex items-center space-x-2 mt-1 text-sm text-gray-500">
                                                <span>{{ $user->followers->count() }} followers</span>
                                                <span>&middot;</span>
                                                <span>{{ $user->following->count() }} following</span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-4 flex justify-between">
                                        <a href="{{ route('profile', $user) }}" class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                                            View Profile
                                        </a>
                                        
                                        @if(!auth()->user()->friends->contains($user->id))
                                            <button class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                                                Add Friend
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        @if($type === 'users')
                            <div class="mt-6">
                                {{ $results['users']->links() }}
                            </div>
                        @elseif($results['users']->hasMorePages())
                            <div class="mt-6 text-center">
                                <button wire:click="$set('type', 'users')" class="text-indigo-600 hover:text-indigo-900">
                                    View all users
                                </button>
                            </div>
                        @endif
                    </div>
                @endif
                
                <!-- Pets Results -->
                @if(($type === 'all' || $type === 'pets') && $results['pets']->count() > 0)
                    <div class="p-6">
                        <h2 class="text-xl font-semibold mb-4">Pets ({{ $results['pets']->total() }})</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($results['pets'] as $pet)
                                <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
                                    <div class="flex items-center space-x-4">
                                        <img src="{{ $pet->profile_photo_url }}" alt="{{ $pet->name }}" class="h-16 w-16 rounded-full">
                                        
                                        <div>
                                            <h3 class="text-lg font-medium">{{ $pet->name }}</h3>
                                            <p class="text-gray-500 text-sm">{{ $pet->type }} &middot; {{ $pet->breed }}</p>
                                            <p class="text-gray-500 text-sm">Owner: {{ $pet->user->name }}</p>
                                        </div>
                                    </div>
                                    
                                    @if($pet->bio)
                                        <p class="mt-2 text-sm text-gray-600">{{ Str::limit($pet->bio, 100) }}</p>
                                    @endif
                                    
                                    <div class="mt-4 flex justify-between">
                                        <a href="{{ route('pet.posts', $pet->id) }}" class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                                            View Profile
                                        </a>
                                        
                                        @if($pet->user_id !== auth()->id())
                                            <button class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                                                Follow
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        @if($type === 'pets')
                            <div class="mt-6">
                                {{ $results['pets']->links() }}
                            </div>
                        @elseif($results['pets']->hasMorePages())
                            <div class="mt-6 text-center">
                                <button wire:click="$set('type', 'pets')" class="text-indigo-600 hover:text-indigo-900">
                                    View all pets
                                </button>
                            </div>
                        @endif
                    </div>
                @endif
                
                <!-- Tags Results -->
                @if(($type === 'all' || $type === 'tags') && $results['tags']->count() > 0)
                    <div class="p-6">
                        <h2 class="text-xl font-semibold mb-4">Tags ({{ $results['tags']->total() }})</h2>
                        <div class="space-y-4">
                            @foreach($results['tags'] as $tag)
                                <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
                                    <div class="flex items-center justify-between">
                                        <h3 class="text-lg font-medium">#{{ $tag->name }}</h3>
                                        <span class="text-gray-500 text-sm">{{ $tag->posts_count ?? $tag->posts->count() }} posts</span>
                                    </div>
                                    
                                    @if($tag->posts->count() > 0)
                                        <div class="mt-3 space-y-3">
                                            @foreach($tag->posts as $post)
                                                <div class="border-t border-gray-100 pt-3">
                                                    <div class="flex items-start space-x-3">
                                                        @if($post->pet_id)
                                                            <img src="{{ $post->pet->profile_photo_url }}" alt="{{ $post->pet->name }}" class="h-8 w-8 rounded-full">
                                                        @else
                                                            <img src="{{ $post->user->profile_photo_url }}" alt="{{ $post->user->name }}" class="h-8 w-8 rounded-full">
                                                        @endif
                                                        
                                                        <div>
                                                            <p class="font-medium text-sm">
                                                                @if($post->pet_id)
                                                                    {{ $post->pet->name }} 
                                                                    <span class="text-gray-500 text-xs">({{ $post->user->name }}'s pet)</span>
                                                                @else
                                                                    {{ $post->user->name }}
                                                                @endif
                                                            </p>
                                                            <p class="text-gray-800 text-sm mt-1">{{ Str::limit($post->content, 100) }}</p>
                                                            <p class="text-gray-500 text-xs mt-1">{{ $post->created_at->diffForHumans() }}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                    
                                    <div class="mt-4">
                                        <a href="{{ route('tag.search') }}?query={{ $tag->name }}" class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                                            View all posts with this tag
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        @if($type === 'tags')
                            <div class="mt-6">
                                {{ $results['tags']->links() }}
                            </div>
                        @elseif($results['tags']->hasMorePages())
                            <div class="mt-6 text-center">
                                <button wire:click="$set('type', 'tags')" class="text-indigo-600 hover:text-indigo-900">
                                    View all tags
                                </button>
                            </div>
                        @endif
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>
