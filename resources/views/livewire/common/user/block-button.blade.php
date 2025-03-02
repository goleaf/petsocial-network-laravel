<button 
    wire:click="toggleBlock" 
    class="inline-flex items-center px-3 py-1.5 rounded-md text-sm font-medium transition-colors duration-150 ease-in-out {{ $isBlocked ? 'bg-red-100 hover:bg-red-200 text-red-700' : 'bg-gray-100 hover:bg-gray-200 text-gray-700' }}"
>
    @if($isBlocked)
        <x-icons.unlock class="h-4 w-4 mr-1.5" stroke-width="2" />
        <span>Unblock</span>
    @else
        <x-icons.ban class="h-4 w-4 mr-1.5" stroke-width="2" />
        <span>Block</span>
    @endif
</button>
