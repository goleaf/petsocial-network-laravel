<div>
    @if($status === 'not_friends')
        <button wire:click="sendRequest" class="btn btn-primary">
            <x-icons.friends class="h-4 w-4 mr-1" stroke-width="2" /> Add {{ $entityType === 'pet' ? 'Pet' : 'Friend' }}
        </button>
    @elseif($status === 'sent_request')
        <div class="dropdown">
            <button class="btn btn-secondary dropdown-toggle" type="button" wire:click="toggleDropdown">
                <x-icons.activity class="h-4 w-4 mr-1" stroke-width="2" /> Request Sent
            </button>
            @if($showDropdown)
                <div class="dropdown-menu show">
                    <a class="dropdown-item" href="#" wire:click.prevent="cancelRequest">
                        <x-icons.back class="h-4 w-4 mr-1" stroke-width="2" /> Cancel Request
                    </a>
                </div>
            @endif
        </div>
    @elseif($status === 'received_request')
        <div class="btn-group">
            <button wire:click="acceptRequest" class="btn btn-success">
                <x-icons.arrow-right class="h-4 w-4 mr-1" stroke-width="2" /> Accept
            </button>
            <button wire:click="declineRequest" class="btn btn-danger">
                <x-icons.back class="h-4 w-4 mr-1" stroke-width="2" /> Decline
            </button>
        </div>
    @elseif($status === 'friends')
        <div class="dropdown">
            <button class="btn btn-success dropdown-toggle" type="button" wire:click="toggleDropdown">
                <x-icons.friends class="h-4 w-4 mr-1" stroke-width="2" /> {{ $entityType === 'pet' ? 'Pet Friend' : 'Friend' }}
            </button>
            @if($showDropdown)
                <div class="dropdown-menu show">
                    <a class="dropdown-item" href="#" wire:click.prevent="removeFriendship">
                        <x-icons.back class="h-4 w-4 mr-1" stroke-width="2" /> Remove {{ $entityType === 'pet' ? 'Pet' : 'Friend' }}
                    </a>
                    <a class="dropdown-item" href="#" wire:click.prevent="blockEntity">
                        <x-icons.back class="h-4 w-4 mr-1" stroke-width="2" /> Block
                    </a>
                </div>
            @endif
        </div>
    @endif
</div>
