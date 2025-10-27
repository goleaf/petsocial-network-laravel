{{-- Minimal group edit form used for automated tests to render the component safely. --}}
<div class="space-y-4">
    {{-- The form mirrors the component bindings so Livewire validation triggers correctly. --}}
    <form wire:submit.prevent="updateGroup" class="grid gap-3">
        <label class="flex flex-col gap-1">
            <span class="text-sm font-medium">{{ __('Group name') }}</span>
            <input type="text" wire:model.lazy="name" class="rounded border px-3 py-2" />
        </label>

        <label class="flex flex-col gap-1">
            <span class="text-sm font-medium">{{ __('Description') }}</span>
            <textarea wire:model.lazy="description" class="rounded border px-3 py-2"></textarea>
        </label>

        <label class="flex flex-col gap-1">
            <span class="text-sm font-medium">{{ __('Visibility') }}</span>
            <select wire:model="visibility" class="rounded border px-3 py-2">
                <option value="open">{{ __('Open') }}</option>
                <option value="closed">{{ __('Closed') }}</option>
                <option value="secret">{{ __('Secret') }}</option>
            </select>
        </label>

        <label class="flex flex-col gap-1">
            <span class="text-sm font-medium">{{ __('Category') }}</span>
            <select wire:model="categoryId" class="rounded border px-3 py-2">
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
        </label>

        <label class="flex flex-col gap-1">
            <span class="text-sm font-medium">{{ __('Location') }}</span>
            <input type="text" wire:model.lazy="location" class="rounded border px-3 py-2" />
        </label>

        <label class="flex flex-col gap-1">
            <span class="text-sm font-medium">{{ __('Cover image') }}</span>
            <input type="file" wire:model="coverImage" accept="image/*" />
        </label>

        <label class="flex flex-col gap-1">
            <span class="text-sm font-medium">{{ __('Icon') }}</span>
            <input type="file" wire:model="icon" accept="image/*" />
        </label>

        <button type="submit" class="rounded bg-indigo-600 px-4 py-2 font-semibold text-white">{{ __('Save group') }}</button>
    </form>
</div>
