@if ($editingPostId)
    <form wire:submit.prevent="update">
        <textarea wire:model="editingContent" class="w-full p-2 border rounded" placeholder="{{ __('posts.edit_your_post') }}"></textarea>
        <input type="text" wire:model="tags" class="w-full p-2 border rounded mt-2" placeholder="{{ __('posts.tags_placeholder') }}">
        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded mt-2">{{ __('posts.update_button') }}</button>
        <button wire:click="$set('editingPostId', null)" class="bg-gray-500 text-white px-4 py-2 rounded mt-2 ml-2">{{ __('posts.cancel_button') }}</button>
    </form>
@else
    <form wire:submit.prevent="save">
        <textarea wire:model="content" class="w-full p-2 border rounded" placeholder="{{ __('posts.whats_on_your_mind') }}"></textarea>
        <input type="text" wire:model="tags" class="w-full p-2 border rounded mt-2" placeholder="{{ __('posts.tags_placeholder') }}">
        <select wire:model="pet_id" class="w-full p-2 border rounded mt-2">
            <option value="">{{ __('posts.post_as_yourself') }}</option>
            @foreach (auth()->user()->pets as $pet)
                <option value="{{ $pet->id }}">{{ __('posts.post_as_pet', ['pet' => $pet->name]) }}</option>
            @endforeach
        </select>
        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded mt-2 w-full">{{ __('posts.post_button') }}</button>
    </form>
@endif
