<div style="display: flex; gap: 10px;">
    @foreach ($reactionTypes as $type => $emoji)
        <button wire:click="react('{{ $type }}')" style="font-size: 20px; border: none; background: none; cursor: pointer;">
            {{ $emoji }} {{ $reactionCounts[$type] ?? 0 }}
        </button>
    @endforeach
    @if ($currentReaction)
        <p>You reacted: {{ $reactionTypes[$currentReaction] }}</p>
    @endif
</div>
