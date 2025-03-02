<div>
    <div class="card">
        <div class="card-header">
            <h5>{{ $entityType === 'pet' ? 'Pet' : 'Friend' }} Suggestions</h5>
        </div>
        <div class="card-body">
            @if($suggestions->isEmpty())
                <p class="text-center">No suggestions available at this time.</p>
            @else
                @foreach($suggestions as $suggestion)
                    <div class="suggestion-item mb-3">
                        <div class="d-flex align-items-center">
                            <div class="suggestion-avatar mr-3">
                                <img src="{{ $suggestion['entity']->avatar ?? '/images/default-avatar.png' }}" 
                                     alt="{{ $suggestion['entity']->name }}" 
                                     class="rounded-circle" 
                                     width="50" height="50">
                            </div>
                            <div class="suggestion-info flex-grow-1">
                                <h6 class="mb-0">{{ $suggestion['entity']->name }}</h6>
                                <small class="text-muted">
                                    {{ $suggestion['mutual_friends_count'] }} mutual {{ $entityType === 'pet' ? 'pet ' : '' }}{{ Str::plural('friend', $suggestion['mutual_friends_count']) }}
                                </small>
                            </div>
                            <div class="suggestion-action">
                                @livewire('common.friend.button', [
                                    'entityType' => $entityType, 
                                    'entityId' => $entity->id, 
                                    'targetId' => $suggestion['entity']->id
                                ], key('suggestion-'.$suggestion['entity']->id))
                            </div>
                        </div>
                        @if($suggestion['mutual_friends_count'] > 0)
                            <div class="mutual-friends mt-2">
                                <small>
                                    <strong>Mutual {{ $entityType === 'pet' ? 'pet ' : '' }}friends:</strong> 
                                    {{ collect($suggestion['mutual_friends'])->take(3)->pluck('name')->join(', ') }}
                                    @if(count($suggestion['mutual_friends']) > 3)
                                        and {{ count($suggestion['mutual_friends']) - 3 }} more
                                    @endif
                                </small>
                            </div>
                        @endif
                    </div>
                @endforeach
            @endif
        </div>
        <div class="card-footer text-center">
            <button wire:click="loadSuggestions" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-sync"></i> Refresh Suggestions
            </button>
        </div>
    </div>
</div>
