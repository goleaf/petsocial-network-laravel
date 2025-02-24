<div class="max-w-md mx-auto bg-white p-6 rounded-lg shadow">
    <h1 class="text-2xl font-bold mb-4">Manage Pets</h1>
    <form wire:submit.prevent="save" enctype="multipart/form-data">
        <div class="mb-4">
            <label class="block text-gray-700">Name</label>
            <input type="text" wire:model="name" class="w-full p-2 border rounded">
        </div>
        <div class="mb-4">
            <label class="block text-gray-700">Type (e.g., Dog, Cat)</label>
            <input type="text" wire:model="type" class="w-full p-2 border rounded">
        </div>
        <div class="mb-4">
            <label class="block text-gray-700">Breed</label>
            <input type="text" wire:model="breed" class="w-full p-2 border rounded">
        </div>
        <div class="mb-4">
            <label class="block text-gray-700">Birthdate</label>
            <input type="date" wire:model="birthdate" class="w-full p-2 border rounded">
        </div>
        <div class="mb-4">
            <label class="block text-gray-700">Avatar</label>
            <input type="file" wire:model="avatar" class="w-full p-2 border rounded">
        </div>
        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded w-full">Add Pet</button>
    </form>
    <h2 class="text-xl font-semibold mt-6">Your Pets</h2>
    @if ($pets->isEmpty())
        <p class="text-gray-500">No pets added yet.</p>
    @else
        <ul class="mt-2">
            @foreach ($pets as $pet)
                <li class="flex items-center justify-between mb-2">
                    <div>
                        <span class="font-bold">{{ $pet->name }}</span>
                        @if ($pet->type) ({{ $pet->type }}, {{ $pet->breed }}) @endif
                        @if ($pet->avatar)
                            <img src="{{ Storage::url($pet->avatar) }}" class="w-12 h-12 rounded-full mt-2">
                        @endif
                    </div>
                    <button wire:click="delete({{ $pet->id }})" class="text-red-500 hover:underline">Delete</button>
                </li>
            @endforeach
        </ul>
    @endif
</div>
