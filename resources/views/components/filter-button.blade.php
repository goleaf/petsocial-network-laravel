<button wire:click="{{ $action }}" class="px-3 py-2 text-sm font-medium rounded-md {{ $isActive ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
    {{ $label }}
</button>
