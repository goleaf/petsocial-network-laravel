<div>
    <button wire:click="toggleLike">
        {{ $isLiked ? 'Unlike' : 'Like' }} ({{ $likeCount }})
    </button>
</div>
