<div>
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5>{{ $entityType === 'pet' ? 'Pet' : '' }} Friends</h5>
                <div class="search-box">
                    <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search friends...">
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="category-filter">
                        <select wire:model="categoryFilter" class="form-control">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category }}">{{ $category }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="bulk-actions">
                        <div class="form-check">
                            <input type="checkbox" wire:model="selectAll" class="form-check-input" id="selectAll">
                            <label class="form-check-label" for="selectAll">Select All</label>
                        </div>
                        <div class="btn-group ml-2">
                            <button wire:click="showCategoryModal" class="btn btn-sm btn-outline-primary" {{ empty($selectedFriends) ? 'disabled' : '' }}>
                                <i class="fas fa-tag"></i> Categorize
                            </button>
                            <button wire:click="removeFriends" class="btn btn-sm btn-outline-danger" {{ empty($selectedFriends) ? 'disabled' : '' }}>
                                <i class="fas fa-trash"></i> Remove
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            @if($friends->isEmpty())
                <div class="text-center py-4">
                    <p>No friends found. {{ $search ? 'Try a different search term.' : '' }}</p>
                </div>
            @else
                <div class="friends-grid">
                    @foreach($friends as $friend)
                        <div class="friend-card {{ in_array($friend->id, $selectedFriends) ? 'selected' : '' }}">
                            <div class="friend-card-header">
                                <div class="form-check">
                                    <input type="checkbox" wire:model="selectedFriends" value="{{ $friend->id }}" 
                                           class="form-check-input" id="friend-{{ $friend->id }}">
                                </div>
                                <img src="{{ $friend->avatar ?? '/images/default-avatar.png' }}" 
                                     alt="{{ $friend->name }}" class="friend-avatar">
                            </div>
                            <div class="friend-card-body">
                                <h6 class="friend-name">{{ $friend->name }}</h6>
                                <p class="friend-username">@{{ $friend->username }}</p>
                                @if($friend->pivot && $friend->pivot->category)
                                    <span class="badge badge-info">{{ $friend->pivot->category }}</span>
                                @endif
                            </div>
                            <div class="friend-card-footer">
                                @livewire('common.friend.button', [
                                    'entityType' => $entityType, 
                                    'entityId' => $entity->id, 
                                    'targetId' => $friend->id
                                ], key('friend-button-'.$friend->id))
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <div class="pagination-container mt-4">
                    {{ $friends->links() }}
                </div>
            @endif
        </div>
    </div>
    
    @if($showCategoryModal)
        <div class="modal fade show" style="display: block;">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Apply Category</h5>
                        <button type="button" class="close" wire:click="cancelCategoryModal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="newCategory">Category Name</label>
                            <input type="text" wire:model="newCategory" class="form-control" id="newCategory" 
                                   placeholder="Enter category name or leave empty to remove category">
                        </div>
                        <p class="text-muted">
                            This will apply the category to {{ count($selectedFriends) }} selected {{ Str::plural('friend', count($selectedFriends)) }}.
                        </p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="cancelCategoryModal">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="applyCategory">Apply</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show"></div>
    @endif
</div>
