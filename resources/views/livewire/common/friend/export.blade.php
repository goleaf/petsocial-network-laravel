<div>
    <div class="card">
        <div class="card-header">
            <h5>Export {{ $entityType === 'pet' ? 'Pet ' : '' }}Friends</h5>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="exportType">Export Type</label>
                        <select wire:model="exportType" id="exportType" class="form-control">
                            <option value="friends">Friends</option>
                            @if($entityType === 'user')
                                <option value="followers">Followers</option>
                                <option value="following">Following</option>
                            @endif
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="exportFormat">Export Format</label>
                        <select wire:model="exportFormat" id="exportFormat" class="form-control">
                            <option value="csv">CSV</option>
                            <option value="json">JSON</option>
                            <option value="vcf">vCard (VCF)</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="form-check">
                        <input type="checkbox" wire:model="includeEmails" class="form-check-input" id="includeEmails">
                        <label class="form-check-label" for="includeEmails">Include Email Addresses</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-check">
                        <input type="checkbox" wire:model="includePhones" class="form-check-input" id="includePhones">
                        <label class="form-check-label" for="includePhones">Include Phone Numbers</label>
                    </div>
                </div>
            </div>
            
            <div class="search-box mb-4">
                <input type="text" wire:model.debounce.300ms="search" class="form-control" 
                       placeholder="Search {{ $exportType }}...">
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>
                                <div class="form-check">
                                    <input type="checkbox" wire:model="selectAll" class="form-check-input" id="selectAll">
                                    <label class="form-check-label" for="selectAll"></label>
                                </div>
                            </th>
                            <th>Name</th>
                            <th>Username</th>
                            @if($includeEmails)
                                <th>Email</th>
                            @endif
                            @if($includePhones)
                                <th>Phone</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td>
                                    <div class="form-check">
                                        <input type="checkbox" wire:model="selectedFriends" value="{{ $user->id }}" 
                                               class="form-check-input" id="user-{{ $user->id }}">
                                        <label class="form-check-label" for="user-{{ $user->id }}"></label>
                                    </div>
                                </td>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->username }}</td>
                                @if($includeEmails)
                                    <td>{{ $user->email }}</td>
                                @endif
                                @if($includePhones)
                                    <td>{{ $user->phone }}</td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ 3 + ($includeEmails ? 1 : 0) + ($includePhones ? 1 : 0) }}" class="text-center">
                                    No {{ $exportType }} found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            <button wire:click="export" class="btn btn-primary" {{ empty($selectedFriends) ? 'disabled' : '' }}>
                <i class="fas fa-download"></i> Export Selected
            </button>
            <span class="text-muted ml-2">
                {{ count($selectedFriends) }} {{ Str::plural('item', count($selectedFriends)) }} selected
            </span>
        </div>
    </div>
</div>
