<div class="flex flex-wrap gap-2">
    @foreach ($reactionTypes as $type => $emoji)
        <button 
            wire:click="react('{{ $type }}')" 
            class="inline-flex items-center px-2 py-1 rounded-full transition-colors {{ $currentReaction === $type ? 'bg-indigo-100 text-indigo-800' : 'bg-gray-100 hover:bg-gray-200 text-gray-800' }}"
        >
            <span class="text-xl mr-1">{{ $emoji }}</span>
            @if(($reactionCounts[$type] ?? 0) > 0)
                <span class="text-xs font-medium">{{ $reactionCounts[$type] ?? 0 }}</span>
            @endif
        </button>
    @endforeach
    @if ($currentReaction)
        <div class="ml-auto text-sm text-gray-500 flex items-center">
            <span class="mr-1">You reacted:</span>
            <span class="text-lg">{{ $reactionTypes[$currentReaction] }}</span>
        </div>
    @endif
</div>
