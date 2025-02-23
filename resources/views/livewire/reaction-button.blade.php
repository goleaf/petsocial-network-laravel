<div>
    <button wire:click="react('like')">Like ({{ $reactionCounts['like'] ?? 0 }})</button>
    <button wire:click="react('love')">Love ({{ $reactionCounts['love'] ?? 0 }})</button>
    <button wire:click="react('haha')">Haha ({{ $reactionCounts['haha'] ?? 0 }})</button>
    @if ($currentReaction)
        <p>You reacted: {{ $currentReaction }}</p>
    @endif
</div>
