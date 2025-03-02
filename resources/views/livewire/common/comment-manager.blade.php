<div class="space-y-4">
    <!-- Comments List -->
    <div class="space-y-4">
        @forelse($comments as $comment)
        <div class="border-t border-gray-100 pt-3">
            <div class="flex space-x-3">
                <img src="{{ $comment->user->profile_photo_url }}" alt="{{ $comment->user->name }}" class="h-8 w-8 rounded-full">
                
                <div class="flex-1">
                    <div class="bg-gray-100 rounded-lg px-4 py-2">
                        <div class="flex justify-between items-start">
                            <span class="font-medium">{{ $comment->user->name }}</span>
                            
                            @if($comment->user_id === auth()->id())
                            <div class="relative" x-data="{ open: false }">
                                <button @click="open = !open" class="text-gray-500 hover:text-gray-700 -mt-1">
                                    <x-icons.dots-vertical class="h-4 w-4" stroke-width="2" />
                                </button>
                                
                                <div x-show="open" @click.away="open = false" class="absolute right-0 mt-1 w-40 bg-white rounded-md shadow-lg z-10">
                                    <button wire:click="edit({{ $comment->id }})" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        Edit
                                    </button>
                                    <button wire:click="delete({{ $comment->id }})" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                                        Delete
                                    </button>
                                </div>
                            </div>
                            @endif
                        </div>
                        
                        <p class="text-gray-800 mt-1">{{ $comment->content }}</p>
                    </div>
                    
                    <div class="flex items-center mt-1 space-x-4 text-xs text-gray-500">
                        <span>{{ $comment->created_at->diffForHumans() }}</span>
                        <button wire:click="reply({{ $comment->id }})" class="hover:text-blue-500">Reply</button>
                    </div>
                    
                    <!-- Replies -->
                    @if($comment->replies->count() > 0)
                    <div class="ml-6 mt-2 space-y-3">
                        @foreach($comment->replies as $reply)
                        <div class="flex space-x-3">
                            <img src="{{ $reply->user->profile_photo_url }}" alt="{{ $reply->user->name }}" class="h-6 w-6 rounded-full">
                            
                            <div class="flex-1">
                                <div class="bg-gray-100 rounded-lg px-3 py-2">
                                    <div class="flex justify-between items-start">
                                        <span class="font-medium text-sm">{{ $reply->user->name }}</span>
                                        
                                        @if($reply->user_id === auth()->id())
                                        <div class="relative" x-data="{ open: false }">
                                            <button @click="open = !open" class="text-gray-500 hover:text-gray-700 -mt-1">
                                                <x-icons.dots-vertical class="h-3 w-3" stroke-width="2" />
                                            </button>
                                            
                                            <div x-show="open" @click.away="open = false" class="absolute right-0 mt-1 w-40 bg-white rounded-md shadow-lg z-10">
                                                <button wire:click="edit({{ $reply->id }})" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                    Edit
                                                </button>
                                                <button wire:click="delete({{ $reply->id }})" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                                                    Delete
                                                </button>
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                    
                                    <p class="text-gray-800 text-sm mt-1">{{ $reply->content }}</p>
                                </div>
                                
                                <div class="flex items-center mt-1 space-x-4 text-xs text-gray-500">
                                    <span>{{ $reply->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                        </div>
                        @endforeach
                        
                        @if($comment->replies->count() > 2 && !$showAllComments)
                        <button wire:click="toggleShowAllComments" class="text-blue-500 text-xs hover:underline">
                            Show more replies
                        </button>
                        @endif
                    </div>
                    @endif
                    
                    <!-- Reply Form -->
                    @if($replyingToId === $comment->id)
                    <div class="mt-2 ml-6">
                        <div class="flex space-x-2">
                            <img src="{{ auth()->user()->profile_photo_url }}" alt="{{ auth()->user()->name }}" class="h-6 w-6 rounded-full">
                            
                            <div class="flex-1">
                                <form wire:submit.prevent="save">
                                    <div class="flex space-x-2">
                                        <input 
                                            wire:model.defer="content" 
                                            class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 text-sm" 
                                            placeholder="Write a reply..."
                                        >
                                        
                                        <button type="submit" class="inline-flex items-center px-3 py-1 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition text-sm">
                                            <x-icons.reply class="h-3 w-3 mr-1" stroke-width="2" />
                                            Reply
                                        </button>
                                        
                                        <button type="button" wire:click="cancelReply" class="inline-flex items-center px-3 py-1 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 transition text-sm">
                                            <x-icons.x class="h-3 w-3 mr-1" stroke-width="2" />
                                            Cancel
                                        </button>
                                    </div>
                                    @error('content') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                </form>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="text-center py-2 text-gray-500 text-sm">
            No comments yet. Be the first to comment!
        </div>
        @endforelse
    </div>
    
    <!-- Show More Comments Button -->
    @if($commentsCount > $comments->count() && !$showAllComments)
    <div class="text-center">
        <button wire:click="toggleShowAllComments" class="text-blue-500 hover:underline text-sm">
            Show all {{ $commentsCount }} comments
        </button>
    </div>
    @endif
    
    <!-- Comment Form -->
    @if(auth()->check())
    <div class="mt-4">
        <div class="flex space-x-3">
            <img src="{{ auth()->user()->profile_photo_url }}" alt="{{ auth()->user()->name }}" class="h-8 w-8 rounded-full">
            
            <div class="flex-1">
                <form wire:submit.prevent="save">
                    <div class="flex space-x-2">
                        <input 
                            wire:model.defer="content" 
                            class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" 
                            placeholder="Write a comment..."
                        >
                        
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition">
                            <x-icons.paper-airplane class="h-4 w-4 mr-1" stroke-width="2" />
                            Post
                        </button>
                    </div>
                    @error('content') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                </form>
            </div>
        </div>
    </div>
    @endif
    
    <!-- Edit Comment Modal -->
    @if($editingCommentId)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-lg">
            <h3 class="text-lg font-medium mb-4">Edit Comment</h3>
            
            <form wire:submit.prevent="update" class="space-y-4">
                <div>
                    <textarea 
                        wire:model.defer="editingContent" 
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" 
                        rows="3"
                    ></textarea>
                    @error('editingContent') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                
                <div class="flex justify-end space-x-2">
                    <button type="button" wire:click="$set('editingCommentId', null)" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 transition">
                        Cancel
                    </button>
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition">
                        <x-icons.check class="h-4 w-4 mr-1" stroke-width="2" />
                        Update
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
