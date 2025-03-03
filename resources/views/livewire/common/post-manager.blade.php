<div class="bg-white shadow rounded-lg p-4 space-y-6">
    <!-- Create Post Form -->
    @if(auth()->check() && ($entityType === 'user' && $entityId === auth()->id()))
    <div class="space-y-4">
        <h2 class="text-xl font-semibold">{{ __('posts.create_post') }}</h2>
        
        <form wire:submit.prevent="save" class="space-y-4">
            <div>
                <textarea 
                    wire:model.defer="content" 
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" 
                    rows="3" 
                    placeholder="{{ __('posts.whats_on_your_mind') }}"
                ></textarea>
                @error('content') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
            
            <div class="flex flex-wrap gap-4">
                <div class="flex-1">
                    <input 
                        wire:model.defer="tags" 
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" 
                        placeholder="{{ __('posts.tags_placeholder') }}"
                    >
                    @error('tags') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                
                @if(count($userPets) > 0)
                <div class="w-48">
                    <select 
                        wire:model.defer="pet_id" 
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                    >
                        <option value="">{{ __('posts.post_as_yourself') }}</option>
                        @foreach($userPets as $pet)
                        <option value="{{ $pet->id }}">{{ __('posts.post_as_pet', ['pet' => $pet->name]) }}</option>
                        @endforeach
                    </select>
                    @error('pet_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                @endif
            </div>
            
            <div class="flex items-center space-x-4">
                <div class="flex-1">
                    <label for="photo" class="flex items-center space-x-2 cursor-pointer">
                        <x-icons.photos class="h-6 w-6 text-gray-500" stroke-width="2" />
                        <span>{{ __('common.add_photo') }}</span>
                        <input id="photo" type="file" wire:model="photo" class="hidden">
                    </label>
                    @error('photo') <span class="text-red-500 text-sm block mt-1">{{ $message }}</span> @enderror
                    
                    @if($photo)
                    <div class="mt-2">
                        <img src="{{ $photo->temporaryUrl() }}" class="h-20 w-auto rounded">
                    </div>
                    @endif
                </div>
                
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition">
                    <x-icons.paper-airplane class="h-4 w-4 mr-1" stroke-width="2" />
                    {{ __('posts.post_button') }}
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
                    {{ __('posts.your_posts') }}
                @else
                    {{ $entity->name }}'s {{ __('posts.posts') }}
                @endif
            @else
                {{ $entity->name }}'s {{ __('posts.posts') }}
            @endif
        </h2>
        
        <div class="flex space-x-2">
            <input 
                wire:model.debounce.300ms="searchTerm" 
                type="text" 
                placeholder="{{ __('posts.search_posts') }}" 
                class="rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
            >
            
            <select 
                wire:model="filter" 
                class="rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
            >
                <option value="all">{{ __('posts.all_posts') }}</option>
                <option value="user">{{ __('posts.user_posts') }}</option>
                @if($entityType === 'user')
                <option value="pets">{{ __('posts.pet_posts') }}</option>
                @if($entityId === auth()->id())
                <option value="friends">{{ __('posts.friend_posts') }}</option>
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
                                <span class="text-gray-500 text-sm">({{ __('posts.pets_owner', ['name' => $post->user->name]) }})</span>
                                @else
                                {{ $post->user->name }}
                                @endif
                            </p>
                            <p class="text-gray-500 text-sm">{{ $post->created_at->diffForHumans() }}</p>
                        </div>
                        
                        @if($post->user_id === auth()->id())
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="text-gray-500 hover:text-gray-700">
                                <x-icons.dots-vertical class="h-5 w-5" stroke-width="2" />
                            </button>
                            
                            <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10">
                                <button wire:click="edit({{ $post->id }})" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    {{ __('posts.edit_post') }}
                                </button>
                                <button wire:click="delete({{ $post->id }})" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                                    {{ __('posts.delete_post') }}
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
                    <img src="{{ Storage::url($post->photo) }}" alt="{{ __('posts.post_image') }}" class="rounded-lg max-h-96 w-auto">
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
                <span>{{ $post->likes->count() }} {{ __('posts.likes') }}</span>
                <span>{{ $post->comments->count() }} {{ __('posts.comments') }}</span>
            </div>
            
            <!-- Post Actions -->
            <div class="mt-3 flex border-t border-b border-gray-200 py-2">
                <button class="flex-1 flex justify-center items-center space-x-2 text-gray-500 hover:text-blue-500">
                    <x-icons.thumb-up class="h-5 w-5" stroke-width="2" />
                    <span>{{ __('posts.like_post') }}</span>
                </button>
                
                <button class="flex-1 flex justify-center items-center space-x-2 text-gray-500 hover:text-blue-500">
                    <x-icons.chat class="h-5 w-5" stroke-width="2" />
                    <span>{{ __('posts.comment_on_post') }}</span>
                </button>
                
                <button class="flex-1 flex justify-center items-center space-x-2 text-gray-500 hover:text-blue-500">
                    <x-icons.share class="h-5 w-5" stroke-width="2" />
                    <span>{{ __('posts.share_post') }}</span>
                </button>
            </div>
            
            <!-- Comments Section -->
            <div class="mt-4">
                <form wire:submit.prevent="addComment({{ $post->id }})" class="flex items-center space-x-2">
                    <input 
                        wire:model.defer="commentText.{{ $post->id }}" 
                        type="text" 
                        placeholder="{{ __('posts.add_comment') }}" 
                        class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                    >
                    <button type="submit" class="inline-flex items-center px-3 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition">
                        <x-icons.paper-airplane class="h-4 w-4" stroke-width="2" />
                    </button>
                </form>
                
                @if($post->comments->count() > 0)
                <div class="mt-3 space-y-3">
                    @foreach($post->comments->take(3) as $comment)
                    <div class="flex items-start space-x-2">
                        <img src="{{ $comment->user->profile_photo_url }}" alt="{{ $comment->user->name }}" class="h-8 w-8 rounded-full">
                        <div class="flex-1 bg-gray-100 rounded-lg p-2">
                            <p class="font-medium text-sm">{{ $comment->user->name }}</p>
                            <p class="text-gray-800 text-sm">{{ $comment->content }}</p>
                            <p class="text-gray-500 text-xs mt-1">{{ $comment->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                    @endforeach
                    
                    @if($post->comments->count() > 3)
                    <button wire:click="viewAllComments({{ $post->id }})" class="text-blue-500 text-sm hover:underline">
                        {{ __('posts.view_all_comments', ['count' => $post->comments->count()]) }}
                    </button>
                    @endif
                </div>
                @endif
            </div>
        </div>
        @empty
        <div class="text-center py-8 text-gray-500">
            @if(!empty($searchTerm))
                {{ __('posts.no_search_results', ['term' => $searchTerm]) }}
            @else
                {{ __('posts.no_posts') }}
            @endif
        </div>
        @endforelse
        
        @if($posts->hasMorePages())
        <div class="flex justify-center mt-6">
            <button wire:click="loadMore" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition">
                {{ __('posts.load_more') }}
            </button>
        </div>
        @endif
    </div>
    
    <!-- Edit Post Modal -->
    @if($editingPostId)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-lg">
            <h3 class="text-xl font-semibold mb-4">{{ __('posts.edit_post') }}</h3>
            
            <form wire:submit.prevent="updatePost" class="space-y-4">
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
                        placeholder="{{ __('posts.tags_placeholder') }}"
                    >
                    @error('editingTags') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                
                <div class="flex justify-end space-x-2">
                    <button type="button" wire:click="cancelEdit" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition">
                        {{ __('posts.cancel_button') }}
                    </button>
                    <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition">
                        {{ __('posts.update_button') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
