<div>
    @if($status === 'not_friends')
        <button wire:click="sendRequest" class="btn btn-primary">
            <i class="fas fa-user-plus"></i> Add {{ $entityType === 'pet' ? 'Pet' : 'Friend' }}
        </button>
    @elseif($status === 'sent_request')
        <div class="dropdown">
            <button class="btn btn-secondary dropdown-toggle" type="button" wire:click="toggleDropdown">
                <i class="fas fa-clock"></i> Request Sent
            </button>
            @if($showDropdown)
                <div class="dropdown-menu show">
                    <a class="dropdown-item" href="#" wire:click.prevent="cancelRequest">
                        <i class="fas fa-times"></i> Cancel Request
                    </a>
                </div>
            @endif
        </div>
    @elseif($status === 'received_request')
        <div class="btn-group">
            <button wire:click="acceptRequest" class="btn btn-success">
                <i class="fas fa-check"></i> Accept
            </button>
            <button wire:click="declineRequest" class="btn btn-danger">
                <i class="fas fa-times"></i> Decline
            </button>
        </div>
    @elseif($status === 'friends')
        <div class="dropdown">
            <button class="btn btn-success dropdown-toggle" type="button" wire:click="toggleDropdown">
                <i class="fas fa-user-check"></i> {{ $entityType === 'pet' ? 'Pet Friend' : 'Friend' }}
            </button>
            @if($showDropdown)
                <div class="dropdown-menu show">
                    <a class="dropdown-item" href="#" wire:click.prevent="removeFriendship">
                        <i class="fas fa-user-minus"></i> Remove {{ $entityType === 'pet' ? 'Pet' : 'Friend' }}
                    </a>
                    <a class="dropdown-item" href="#" wire:click.prevent="blockEntity">
                        <i class="fas fa-ban"></i> Block
                    </a>
                </div>
            @endif
        </div>
    @endif
</div>
