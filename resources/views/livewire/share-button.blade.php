<div>
    <button wire:click="share" class="text-gray-600 hover:text-gray-800">
        {{ $isShared ? 'Unshare' : 'Share' }} ({{ $shareCount }})
    </button>
</div>
