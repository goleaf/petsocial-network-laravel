<div class="max-w-lg mx-auto bg-white p-6 rounded-lg shadow">
    <h1 class="text-2xl font-bold mb-4 text-center">Manage Pets</h1>
    <form wire:submit.prevent="save" enctype="multipart/form-data">
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold mb-2">Name</label>
            <input type="text" wire:model="name" class="w-full p-3 border rounded-lg">
        </div>
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold mb-2">Type (e.g., Dog, Cat)</label>
            <input type="text" wire:model="type" class="w-full p-3 border rounded-lg">
        </div>
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold mb-2">Breed</label>
            <input type="text" wire:model="breed" class="w-full p-3 border rounded-lg">
        </div>
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold mb-2">Birthdate</label>
            <input type="date" wire:model="birthdate" class="w-full p-3 border rounded-lg">
        </div>
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold mb-2">Location</label>
            <input type="text" wire:model="location" class="w-full p-3 border rounded-lg" placeholder="e.g., New York, NY">
        </div>
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold mb-2">Avatar</label>
            <input type="file" wire:model="avatar" class="w-full p-2 border rounded-lg">
        </div>
        <button type="submit" class="w-full bg-blue-500 text-white px-4 py-3 rounded-lg hover:bg-blue-600">Add Pet</button>
    </form>
    <h2 class="text-xl font-semibold mt-6">Your Pets</h2>
    @if ($pets->isEmpty())
        <p class="text-gray-500 text-center">No pets added yet.</p>
    @else
        <ul class="mt-2 grid grid-cols-1 gap-2">
            @foreach ($pets as $pet)
                <li class="flex flex-col sm:flex-row items-center justify-between">
                    <div class="text-center sm:text-left">
                        <span class="font-bold">{{ $pet->name }}</span>
                        @if ($pet->type) ({{ $pet->type }}, {{ $pet->breed }}) @endif
                        @if ($pet->location) <span class="text-gray-500"> - {{ $pet->location }}</span> @endif
                        @if ($pet->avatar)
                            <img src="{{ Storage::url($pet->avatar) }}" class="w-12 h-12 rounded-full mt-2 mx-auto sm:ml-0">
                        @endif
                    </div>
                    <button wire:click="delete({{ $pet->id }})" class="text-red-500 hover:underline mt-2 sm:mt-0">Delete</button>
                </li>
            @endforeach
        </ul>
    @endif
</div>
