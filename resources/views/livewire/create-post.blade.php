<div class="bg-white rounded-lg shadow-md p-4 mb-6">
    <div>
        @if($draftMode)
            <div class="bg-blue-50 text-blue-700 px-3 py-2 rounded-md mb-3 text-sm flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                </svg>
                Draft saved automatically
            </div>
        @endif
        
        <!-- Content Warning Messages -->
        @if(count($contentWarnings) > 0)
            <div class="bg-yellow-50 text-yellow-700 px-3 py-2 rounded-md mb-3 text-sm">
                @foreach($contentWarnings as $warning)
                    <div class="flex items-center mb-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        {{ $warning }}
                    </div>
                @endforeach
            </div>
        @endif

        <!-- Post Content Area -->
        <form wire:submit.prevent="{{ $editingPostId ? 'update' : 'save' }}">
            <div class="mb-4 relative">
                <textarea 
                    class="w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
                    wire:model.debounce.500ms="{{ $editingPostId ? 'editingContent' : 'content' }}"
                    rows="3"
                    placeholder="What's on your pet's mind?"
                ></textarea>
                
                <!-- Character Counter -->
                <div class="text-xs text-gray-500 text-right mt-1">
                    {{ $contentLength }}/{{ $maxLength }} characters
                </div>
                
                <!-- Mention Dropdown -->
                @if($showMentionDropdown && count($mentionResults) > 0)
                    <div class="absolute z-10 mt-1 w-full max-w-md bg-white shadow-lg rounded-md overflow-hidden">
                        @foreach($mentionResults as $user)
                            <div 
                                class="px-4 py-2 hover:bg-gray-100 cursor-pointer flex items-center"
                                wire:click="selectMention('{{ $user['username'] }}')"
                            >
                                @if($user['profile_photo_path'])
                                    <img src="{{ Storage::url($user['profile_photo_path']) }}" 
                                        class="h-6 w-6 rounded-full mr-2" 
                                        alt="{{ $user['name'] }}"
                                    >
                                @else
                                    <div class="h-6 w-6 rounded-full bg-gray-200 mr-2 flex items-center justify-center">
                                        {{ substr($user['name'], 0, 1) }}
                                    </div>
                                @endif
                                <span>{{ $user['name'] }} (@{{ $user['username'] }})</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
            
            <!-- Tags Input -->
            <div class="mb-4 relative">
                <input 
                    type="text" 
                    class="w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm" 
                    wire:model.debounce.300ms="tags" 
                    placeholder="Add tags (comma separated)"
                >
                
                <!-- Tag Dropdown -->
                @if($showTagDropdown && count($matchingTags) > 0)
                    <div class="absolute z-10 mt-1 w-full bg-white shadow-lg rounded-md overflow-hidden">
                        @foreach($matchingTags as $tag)
                            <div 
                                class="px-4 py-2 hover:bg-gray-100 cursor-pointer"
                                wire:click="addTag('{{ $tag }}')"
                            >
                                #{{ $tag }}
                            </div>
                        @endforeach
                    </div>
                @endif
                
                <!-- Popular Tags -->
                @if(count($popularTags) > 0)
                    <div class="mt-2 flex flex-wrap gap-2">
                        @foreach($popularTags as $tag)
                            <span 
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 cursor-pointer hover:bg-gray-200"
                                wire:click="addTag('{{ $tag }}')"
                            >
                                #{{ $tag }}
                            </span>
                        @endforeach
                    </div>
                @endif
            </div>
            
            <!-- Image Upload -->
            <div class="mb-4">
                <label for="images" class="flex items-center space-x-2 cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span class="text-gray-700">Add Photos</span>
                </label>
                <input 
                    id="images" 
                    type="file" 
                    wire:model="images" 
                    class="hidden" 
                    multiple 
                    accept="image/*"
                >
                
                <!-- Image Preview -->
                @if(count($temporaryImages) > 0)
                    <div class="mt-2 grid grid-cols-3 gap-2">
                        @foreach($temporaryImages as $index => $image)
                            <div class="relative">
                                <img src="{{ $image['url'] }}" class="h-24 w-full object-cover rounded-md">
                                <button 
                                    type="button" 
                                    wire:click="removeImage({{ $index }})" 
                                    class="absolute top-1 right-1 bg-red-500 text-white rounded-full p-1"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        @endforeach
                    </div>
                @endif
                
                <!-- Upload Indicator -->
                <div wire:loading wire:target="images" class="mt-2 text-sm text-gray-500">
                    Uploading...
                </div>
            </div>
            
            <!-- Pet Selection -->
            <div class="mb-4">
                <select 
                    wire:model="pet_id" 
                    class="w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
                >
                    <option value="">Post as yourself</option>
                    @foreach($pets as $pet)
                        <option value="{{ $pet->id }}">Post as {{ $pet->name }}</option>
                    @endforeach
                </select>
            </div>
            
            <!-- Visibility Options -->
            <div class="mb-4">
                <div class="flex space-x-4">
                    <label class="inline-flex items-center">
                        <input type="radio" wire:model="visibility" value="public" class="form-radio text-indigo-600">
                        <span class="ml-2">Public</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="radio" wire:model="visibility" value="friends" class="form-radio text-indigo-600">
                        <span class="ml-2">Friends</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="radio" wire:model="visibility" value="private" class="form-radio text-indigo-600">
                        <span class="ml-2">Private</span>
                    </label>
                </div>
            </div>
            
            <!-- Submit Button -->
            <div class="flex justify-end">
                @if($editingPostId)
                    <button 
                        type="button" 
                        wire:click="resetEditingState" 
                        class="mr-2 px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300"
                    >
                        Cancel
                    </button>
                @endif
                <button 
                    type="submit" 
                    class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
                    wire:loading.attr="disabled"
                >
                    <span wire:loading.remove wire:target="{{ $editingPostId ? 'update' : 'save' }}">
                        {{ $editingPostId ? 'Update Post' : 'Post' }}
                    </span>
                    <span wire:loading wire:target="{{ $editingPostId ? 'update' : 'save' }}">
                        {{ $editingPostId ? 'Updating...' : 'Posting...' }}
                    </span>
                </button>
            </div>
        </form>
    </div>
</div>