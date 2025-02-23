<div>
    <h1 class="text-2xl font-bold mb-4">Welcome, {{ auth()->user()->name }}!</h1>
    @livewire('create-post')
    <h2 class="text-xl font-semibold mt-6 mb-2">Recent Posts</h2>
    @foreach ($posts as $post)
        <div class="bg-white p-4 rounded-lg shadow mb-4">
            <div class="flex flex-col sm:flex-row sm:items-center">
                <strong class="text-lg">{{ $post->user->name }}</strong>
                @if ($post->user->id !== auth()->id())
                    <div class="mt-2 sm:mt-0 sm:ml-2 flex space-x-2">
                        @livewire('follow-button', ['userId' => $post->user->id], key('follow-'.$post->id))
                        @livewire('block-button', ['userId' => $post->user->id], key('block-'.$post->id))
                    </div>
                @endif
            </div>
            <p class="mt-2">{!! $post->formattedContent() !!}</p>
            @if ($post->tags->isNotEmpty())
                <p class="text-sm text-gray-500">Tags: {{ $post->tags->pluck('name')->implode(', ') }}</p>
            @endif
            <small class="text-gray-400">{{ $post->created_at->diffForHumans() }}</small>
            @if ($post->user->id === auth()->id())
                <div class="mt-2 flex space-x-2">
                    <button wire:click="$emit('edit', {{ $post->id }})" class="text-blue-500 hover:underline">Edit</button>
                    <button wire:click="$emit('delete', {{ $post->id }})" class="text-red-500 hover:underline">Delete</button>
                </div>
            @endif
            <div class="mt-2 flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-4">
                @livewire('reaction-button', ['postId' => $post->id], key('reactions-'.$post->id))
                @livewire('share-button', ['postId' => $post->id], key('shares-'.$post->id))
                @livewire('report-post', ['postId' => $post->id], key('report-'.$post->id))
            </div>
            @livewire('comment-section', ['postId' => $post->id], key('comments-'.$post->id))
        </div>
    @endforeach
    {{ $posts->links() }}
</div>
