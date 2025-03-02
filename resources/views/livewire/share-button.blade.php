<div>
    <button wire:click="share" class="inline-flex items-center px-3 py-1.5 rounded-md transition-colors {{ $isShared ? 'text-blue-600 hover:text-blue-700 bg-blue-50 hover:bg-blue-100' : 'text-gray-600 hover:text-gray-700 bg-gray-50 hover:bg-gray-100' }}">
        <x-icons.share class="h-5 w-5 mr-1.5 {{ $isShared ? 'text-blue-600' : '' }}" stroke-width="1.5" />
        <span>{{ $isShared ? 'Unshare' : 'Share' }}</span>
        @if($shareCount > 0)
            <span class="ml-1.5 px-2 py-0.5 bg-{{ $isShared ? 'blue' : 'gray' }}-100 text-{{ $isShared ? 'blue' : 'gray' }}-800 text-xs font-medium rounded-full">{{ $shareCount }}</span>
        @endif
    </button>
</div>
