<div class="max-w-md mx-auto bg-white p-6 rounded-lg shadow">
    @if (session()->has('message'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
            {{ session('error') }}
        </div>
    @endif

    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">{{ $tab === 'followers' ? ($entityType === 'user' ? 'Your Followers' : $entity->name . '\'s Followers') : ($entityType === 'user' ? 'Following' : $entity->name . '\'s Following') }}</h1>
        <div class="flex space-x-2">
            <button wire:click="setTab('followers')" class="px-4 py-2 rounded-lg {{ $tab === 'followers' ? 'bg-blue-500 text-white' : 'bg-gray-200' }}">
                Followers
            </button>
            <button wire:click="setTab('following')" class="px-4 py-2 rounded-lg {{ $tab === 'following' ? 'bg-blue-500 text-white' : 'bg-gray-200' }}">
                Following
            </button>
        </div>
    </div>

    <input type="text" wire:model.debounce.500ms="search" class="w-full p-3 border rounded-lg mb-4" placeholder="Search by name...">

    @if ($users->isEmpty())
        <p class="text-gray-500 text-center py-4">No {{ $tab === 'followers' ? 'followers' : 'following' }} found.</p>
    @else
        @if (count($selectedUsers) > 0)
            <div class="mb-4 flex justify-end space-x-2">
                @if ($tab === 'followers')
                    <button wire:click="bulkRemoveFollowers" class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600">
                        Remove Selected
                    </button>
                @else
                    <button wire:click="bulkUnfollow" class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600">
                        Unfollow Selected
                    </button>
                @endif
            </div>
        @endif

        <ul class="divide-y divide-gray-200">
            @foreach ($users as $user)
                <li class="py-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <input type="checkbox" wire:model="selectedUsers" value="{{ $user->id }}" class="h-4 w-4 text-blue-600">
                            <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center">
                                <span class="text-gray-600 font-bold">{{ substr($user->name, 0, 1) }}</span>
                            </div>
                            <a href="{{ route('profile', $user) }}" class="text-blue-500 hover:underline font-medium">{{ $user->name }}</a>
                        </div>
                        <div class="flex space-x-2">
                            @if ($tab === 'followers')
                                <button wire:click="removeFollower({{ $user->id }})" class="px-3 py-1 bg-red-100 hover:bg-red-200 text-red-700 rounded-lg text-sm">
                                    Remove
                                </button>
                                @if (!auth()->user()->isFollowing($user))
                                    <button wire:click="follow({{ $user->id }})" class="px-3 py-1 bg-blue-100 hover:bg-blue-200 text-blue-700 rounded-lg text-sm">
                                        Follow Back
                                    </button>
                                @endif
                            @else
                                <button wire:click="unfollow({{ $user->id }})" class="px-3 py-1 bg-red-100 hover:bg-red-200 text-red-700 rounded-lg text-sm">
                                    Unfollow
                                </button>
                                <button wire:click="toggleNotifications({{ $user->id }})" class="px-3 py-1 {{ $user->pivot->notify ? 'bg-green-100 hover:bg-green-200 text-green-700' : 'bg-gray-100 hover:bg-gray-200 text-gray-700' }} rounded-lg text-sm">
                                    {{ $user->pivot->notify ? 'Notifications On' : 'Notifications Off' }}
                                </button>
                            @endif
                        </div>
                    </div>
                </li>
            @endforeach
        </ul>

        <div class="mt-4">
            {{ $users->links() }}
        </div>
    @endif
</div>
