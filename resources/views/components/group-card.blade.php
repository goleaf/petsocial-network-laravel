<div class="bg-white border border-gray-200 rounded-lg overflow-hidden shadow-sm hover:shadow-md transition-shadow duration-200">
    <div class="h-32 bg-cover bg-center" style="background-image: url('{{ $coverImage }}')"></div>
    
    <div class="p-4">
        <div class="flex items-center mb-3">
            @if ($icon)
                <img src="{{ $icon }}" alt="{{ $name }}" class="h-10 w-10 rounded-full mr-3">
            @else
                <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center mr-3">
                    <span class="text-lg font-bold text-gray-600">{{ substr($name, 0, 1) }}</span>
                </div>
            @endif
            
            <div>
                <h3 class="text-lg font-bold text-gray-900">{{ $name }}</h3>
                <p class="text-sm text-gray-500">{{ $membersCount }} {{ Str::plural('member', $membersCount) }}</p>
            </div>
        </div>
        
        <p class="text-gray-700 text-sm mb-4 line-clamp-2">{{ $description }}</p>
        
        <div class="flex items-center justify-between">
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $visibility === 'open' ? 'bg-green-100 text-green-800' : ($visibility === 'closed' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                {{ ucfirst($visibility) }}
            </span>
            
            <div>
                @if ($isMember)
                    <button wire:click="leaveGroup({{ $groupId }})" class="text-sm text-red-600 hover:text-red-800">
                        Leave
                    </button>
                @else
                    <button wire:click="joinGroup({{ $groupId }})" class="text-sm font-medium text-blue-600 hover:text-blue-800">
                        {{ $visibility === 'open' ? 'Join' : 'Request to Join' }}
                    </button>
                @endif
            </div>
        </div>
    </div>
    
    <div class="bg-gray-50 px-4 py-3 border-t border-gray-200">
        <a href="{{ route('group.detail', $groupId) }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
            View Group
        </a>
    </div>
</div>
