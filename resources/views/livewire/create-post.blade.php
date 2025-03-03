@if ($editingPostId)
    <form wire:submit.prevent="update" class="bg-white p-4 rounded-lg shadow">
        <textarea 
            wire:model="editingContent" 
            class="w-full p-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500" 
            placeholder="{{ __('posts.edit_your_post') }}"
        ></textarea>
        @error('editingContent') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        
        <input 
            type="text" 
            wire:model="tags" 
            class="w-full p-2 border rounded mt-2 focus:outline-none focus:ring-2 focus:ring-blue-500" 
            placeholder="{{ __('posts.tags_placeholder') }}"
        >
        @error('tags') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        
        <div class="flex mt-2">
            <button 
                type="submit" 
                class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded transition duration-200"
            >
                {{ __('posts.update_button') }}
            </button>
            <button 
                type="button"
                wire:click="$set('editingPostId', null)" 
                class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded ml-2 transition duration-200"
            >
                {{ __('posts.cancel_button') }}
            </button>
        </div>
    </form>
@else
    <form wire:submit.prevent="save" class="bg-white p-4 rounded-lg shadow">
        <textarea 
            wire:model="content" 
            class="w-full p-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500" 
            placeholder="{{ __('posts.whats_on_your_mind') }}"
        ></textarea>
        @error('content') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        
        <input 
            type="text" 
            wire:model="tags" 
            class="w-full p-2 border rounded mt-2 focus:outline-none focus:ring-2 focus:ring-blue-500" 
            placeholder="{{ __('posts.tags_placeholder') }}"
        >
        @error('tags') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        
        <select 
            wire:model="pet_id" 
            class="w-full p-2 border rounded mt-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
            <option value="">{{ __('posts.post_as_yourself') }}</option>
            @foreach (auth()->user()->pets as $pet)
                <option value="{{ $pet->id }}">{{ $pet->name }}</option>
            @endforeach
        </select>
        @error('pet_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        
        <select 
            wire:model="visibility" 
            class="w-full p-2 border rounded mt-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
            <option value="public">{{ __('posts.visibility_public') }}</option>
            <option value="friends">{{ __('posts.visibility_friends') }}</option>
            <option value="private">{{ __('posts.visibility_private') }}</option>
        </select>
        @error('visibility') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        
        <button 
            type="submit" 
            class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded mt-2 w-full transition duration-200"
        >
            {{ __('posts.post_button') }}
        </button>
    </form>
@endif