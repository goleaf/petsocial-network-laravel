@if ($editingPostId)
    <form wire:submit.prevent="update">
        <textarea wire:model="editingContent" class="w-full p-2 border rounded" placeholder="Edit your post..."></textarea>
        <input type="text" wire:model="tags" class="w-full p-2 border rounded mt-2" placeholder="Tags (comma-separated)">
        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded mt-2">Update</button>
        <button wire:click="$set('editingPostId', null)" class="bg-gray-500 text-white px-4 py-2 rounded mt-2 ml-2">Cancel</button>
    </form>
@else
    <form wire:submit.prevent="save">
        <textarea wire:model="content" class="w-full p-2 border rounded" placeholder="What’s on your mind?"></textarea>
        <input type="text" wire:model="tags" class="w-full p-2 border rounded mt-2" placeholder="Tags (comma-separated)">
        <select wire:model="pet_id" class="w-full p-2 border rounded mt-2">
            <option value="">Post as yourself</option>
            @foreach (auth()->user()->pets as $pet)
                <option value="{{ $pet->id }}">{{ $pet->name }}</option>
            @endforeach
        </select>
        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded mt-2 w-full">Post</button>
    </form>
@endif
