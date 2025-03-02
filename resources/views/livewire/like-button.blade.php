<div>
    <button wire:click="toggleLike" class="inline-flex items-center px-3 py-1.5 rounded-md transition-colors {{ $isLiked ? 'text-red-600 hover:text-red-700 bg-red-50 hover:bg-red-100' : 'text-gray-600 hover:text-gray-700 bg-gray-50 hover:bg-gray-100' }}">
        @if($isLiked)
            <x-icons.heart class="h-5 w-5 mr-1.5 text-red-600" fill="currentColor" stroke-width="1.5" />
        @else
            <x-icons.heart class="h-5 w-5 mr-1.5" stroke-width="1.5" />
        @endif
        <span>{{ $isLiked ? 'Unlike' : 'Like' }}</span>
        @if($likeCount > 0)
            <span class="ml-1.5 px-2 py-0.5 bg-{{ $isLiked ? 'red' : 'gray' }}-100 text-{{ $isLiked ? 'red' : 'gray' }}-800 text-xs font-medium rounded-full">{{ $likeCount }}</span>
        @endif
    </button>
</div>
