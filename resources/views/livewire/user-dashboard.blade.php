<div class="space-y-6">
    <div class="bg-white p-6 rounded-lg shadow">
        <h1 class="text-2xl font-bold text-gray-800 mb-2 text-center sm:text-left">Welcome, {{ auth()->user()->name }}!</h1>
        <p class="text-gray-600 text-center sm:text-left">Your news feed with updates from friends and followed users.</p>
    </div>
    <div class="bg-white p-6 rounded-lg shadow">
        @livewire('content.create-post')
    </div>
    <div>
        <h2 class="text-xl font-semibold text-gray-800 mb-4 text-center sm:text-left">News Feed</h2>
        @if ($posts->isEmpty())
            <div class="bg-white p-6 rounded-lg shadow text-center text-gray-500">
                No posts to show yet. Add friends or follow users to fill your feed!
            </div>
        @else
            <div class="grid grid-cols-1 gap-6">
                @foreach ($posts as $post)
                    <div class="bg-white p-6 rounded-lg shadow hover:shadow-md transition-shadow">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-4">
                            <div class="flex items-center">
                                @if ($post->user->profile->avatar && !$post->pet)
                                    <img src="{{ Storage::url($post->user->profile->avatar) }}" class="w-10 h-10 rounded-full mr-3">
                                @elseif ($post->pet && $post->pet->avatar)
                                    <img src="{{ Storage::url($post->pet->avatar) }}" class="w-10 h-10 rounded-full mr-3">
                                @else
                                    <div class="w-10 h-10 rounded-full bg-gray-200 mr-3 flex items-center justify-center text-gray-500">?</div>
                                @endif
                                <div>
                                    <strong class="text-lg text-gray-800">
                                        <a href="{{ route('profile', $post->user) }}" class="hover:underline">
                                            {{ $post->pet ? $post->pet->name : $post->user->name }}
                                        </a>
                                    </strong>
                                    @if ($post->pet)
                                        <span class="text-sm text-gray-500">by {{ $post->user->name }}</span>
                                    @endif
                                    @if ($post->shares->contains('user_id', auth()->id()))
                                        <span class="text-sm text-gray-500 ml-2">[Shared by you]</span>
                                    @endif
                                    <p class="text-sm text-gray-400">{{ $post->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                            @if ($post->user->id !== auth()->id())
                                <div class="mt-2 sm:mt-0 flex space-x-2">
                                    @livewire('common.friend.button', ['entityType' => 'user', 'entityId' => auth()->id(), 'targetId' => $post->user->id], key('friend-'.$post->id))
                                    @livewire('follow.button', ['entityType' => 'user', 'entityId' => auth()->id(), 'targetId' => $post->user->id], key('follow-'.$post->id))
                                    @livewire('block-button', ['userId' => $post->user->id], key('block-'.$post->id))
                                </div>
                            @endif
                        </div>
                        <p class="text-gray-700 mb-3">{!! $post->formattedContent() !!}</p>
                        @if ($post->tags->isNotEmpty())
                            <p class="text-sm text-gray-500 mb-2">Tags:
                                @foreach ($post->tags as $tag)
                                    <a href="{{ route('tag.search') }}?search={{ $tag->name }}" class="text-blue-500 hover:underline">#{{ $tag->name }}</a>
                                @endforeach
                            </p>
                        @endif
                        <div class="flex flex-col sm:flex-row sm:items-center gap-4 mb-4">
                            @livewire('content.reaction-button', ['postId' => $post->id], key('reactions-'.$post->id))
                            @livewire('content.share-button', ['postId' => $post->id], key('shares-'.$post->id))
                            @if ($post->user->id === auth()->id())
                                <div class="flex space-x-2">
                                    <button wire:click="$emit('edit', {{ $post->id }})" class="text-blue-500 hover:underline">Edit</button>
                                    <button wire:click="$emit('delete', {{ $post->id }})" class="text-red-500 hover:underline">Delete</button>
                                </div>
                            @else
                                @livewire('content.report-post', ['postId' => $post->id], key('report-'.$post->id))
                            @endif
                        </div>
                        @livewire('content.comment-section', ['postId' => $post->id], key('comments-'.$post->id))
                    </div>
                @endforeach
            </div>
            <div class="mt-6 flex justify-center">{{ $posts->links() }}</div>
        @endif
    </div>
</div>
