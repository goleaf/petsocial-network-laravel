<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <div class="bg-white shadow-sm rounded-lg overflow-hidden p-6">
        <!-- Group Header -->
        <div class="mb-8">
            <div class="h-48 bg-cover bg-center rounded-lg" style="background-image: url('{{ $group->cover_image ? Storage::url($group->cover_image) : 'https://via.placeholder.com/1200x300?text=Group+Cover' }}')"></div>
            
            <div class="mt-6 flex flex-col md:flex-row md:items-center md:justify-between">
                <div class="flex items-center space-x-4">
                    <div class="flex-shrink-0">
                        @if ($group->icon)
                            <img src="{{ Storage::url($group->icon) }}" alt="{{ $group->name }}" class="h-16 w-16 rounded-full">
                        @else
                            <div class="h-16 w-16 rounded-full bg-gray-300 flex items-center justify-center">
                                @text('{{ substr($group->name, 0, 1) }}')
                            </div>
                        @endif
                    </div>
                    
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">{{ $group->name }}</h1>
                        <p class="text-sm text-gray-500">{{ $group->members_count }} {{ Str::plural('member', $group->members_count) }}</p>
                    </div>
                </div>
                
                <div class="mt-4 md:mt-0 flex space-x-2">
                    @button([
                        'action' => "$toggle('showEditModal')",
                        'label' => 'Edit Group',
                        'variant' => 'secondary',
                        'icon' => 'heroicon-o-pencil',
                        'disabled' => !$canEditGroup
                    ])
                    
                    @button([
                        'action' => "$toggle('showInviteModal')",
                        'label' => 'Invite Members',
                        'variant' => 'primary',
                        'icon' => 'heroicon-o-user-add',
                        'disabled' => !$canInviteMembers
                    ])
                </div>
            </div>
        </div>
        
        <!-- Group Content -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Column -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Description -->
                <div>
                    <h2 class="text-lg font-bold text-gray-900 mb-2">About This Group</h2>
                    <div class="prose max-w-none text-gray-700">
                        {!! $group->description !!}
                    </div>
                </div>
                
                <!-- Topics -->
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-bold text-gray-900">Topics</h2>
                        @button([
                            'action' => "$toggle('showCreateTopicModal')",
                            'label' => 'New Topic',
                            'variant' => 'primary',
                            'icon' => 'heroicon-o-plus',
                            'disabled' => !$canCreateTopics
                        ])
                    </div>
                    
                    <div class="space-y-3">
                        @forelse ($topics as $topic)
                            <div class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow duration-200">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h3 class="text-lg font-medium text-gray-900">{{ $topic->title }}</h3>
                                        <p class="text-sm text-gray-500">Started by {{ $topic->author->name }} · {{ $topic->created_at->diffForHumans() }}</p>
                                    </div>
                                    
                                    <div class="flex items-center space-x-4">
                                        <div class="text-sm text-gray-700 flex items-center space-x-1">
                                            @svg('heroicon-o-chat-alt', 'h-4 w-4')
                                            <span>{{ $topic->replies_count }}</span>
                                        </div>
                                        
                                        <div class="text-sm text-gray-700 flex items-center space-x-1">
                                            @svg('heroicon-o-eye', 'h-4 w-4')
                                            <span>{{ $topic->views_count }}</span>
                                        </div>
                                    </div>
                                </div>
                                
                                @if ($topic->tags)
                                    <div class="mt-3 flex flex-wrap gap-2">
                                        @foreach ($topic->tags as $tag)
                                            @badge(['color' => 'gray', 'text' => $tag])
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @empty
                            <div class="text-center py-6">
                                <p class="text-gray-500">No topics found. Be the first to start a discussion!</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
            
            <!-- Right Column -->
            <div class="space-y-6">
                <!-- Members -->
                <div>
                    <h2 class="text-lg font-bold text-gray-900 mb-4">Members</h2>
                    <div class="space-y-3">
                        @forelse ($members as $member)
                            <div class="bg-white border border-gray-200 rounded-lg p-4 flex items-center space-x-4 hover:shadow-md transition-shadow duration-200">
                                <div class="flex-shrink-0">
                                    <img src="{{ $member->avatar_url }}" alt="{{ $member->name }}" class="h-12 w-12 rounded-full">
                                </div>
                                
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ $member->name }}</p>
                                    <p class="text-sm text-gray-500 truncate">{{ $member->pivot->role }}</p>
                                </div>
                                
                                @if ($showActions)
                                    <div class="flex items-center space-x-2">
                                        @if ($isOwner)
                                            <button wire:click="removeMember({{ $member->id }})" class="text-sm text-red-600 hover:text-red-800">
                                                Remove
                                            </button>
                                        @elseif ($isAdmin && !$isOwner)
                                            <button wire:click="promoteToAdmin({{ $member->id }})" class="text-sm text-blue-600 hover:text-blue-800">
                                                Promote
                                            </button>
                                            <button wire:click="removeMember({{ $member->id }})" class="text-sm text-red-600 hover:text-red-800">
                                                Remove
                                            </button>
                                        @elseif ($isCurrentUser)
                                            <button wire:click="leaveGroup" class="text-sm text-red-600 hover:text-red-800">
                                                Leave
                                            </button>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @empty
                            <p class="text-gray-500">No members found.</p>
                        @endforelse
                    </div>
                </div>
                
                <!-- Group Info -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-gray-900 mb-2">Group Information</h3>
                    <div class="space-y-2 text-sm text-gray-700">
                        <div class="flex items-center space-x-2">
                            @svg('heroicon-o-clock', 'h-5 w-5 text-gray-400')
                            <span>Created {{ $group->created_at->diffForHumans() }}</span>
                        </div>
                        
                        <div class="flex items-center space-x-2">
                            @svg('heroicon-o-calendar', 'h-5 w-5 text-gray-400')
                            <span>{{ $group->events_count }} upcoming events</span>
                        </div>
                        
                        <div class="flex items-center space-x-2">
                            @svg('heroicon-o-lock-closed', 'h-5 w-5 text-gray-400')
                            <span>{{ $group->visibility === 'open' ? 'Open to join' : ($group->visibility === 'closed' ? 'Request to join' : 'Private group') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modals -->
    @include('livewire.group.partials.create-topic-modal')
    @include('livewire.group.partials.edit-group-modal')
    @include('livewire.group.partials.invite-members-modal')
</div>
