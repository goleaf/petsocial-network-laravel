<button 
    wire:click="toggleBlock" 
    class="px-3 py-1 rounded-lg text-sm {{ $isBlocked ? 'bg-red-100 hover:bg-red-200 text-red-700' : 'bg-gray-100 hover:bg-gray-200 text-gray-700' }}"
>
    {{ $isBlocked ? 'Unblock' : 'Block' }}
</button>
