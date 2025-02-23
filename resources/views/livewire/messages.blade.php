<div class="flex">
    <div class="w-1/4 bg-white p-4 rounded-lg shadow">
        <h2 class="text-xl font-semibold mb-4">Conversations</h2>
        @foreach ($conversations as $user)
            <div class="p-2 hover:bg-gray-100 cursor-pointer" wire:click="selectConversation({{ $user->id }})">
                {{ $user->name }}
            </div>
        @endforeach
    </div>
    <div class="w-3/4 ml-4">
        @if ($receiverId)
            <div class="bg-white p-4 rounded-lg shadow h-96 overflow-y-auto">
                @foreach ($messages as $message)
                    <div class="{{ $message->sender_id === auth()->id() ? 'text-right' : 'text-left' }} mb-2">
                        <p class="inline-block p-2 rounded-lg {{ $message->sender_id === auth()->id() ? 'bg-blue-100' : 'bg-gray-100' }}">
                            {{ $message->content }}
                        </p>
                        <small class="block text-gray-400">{{ $message->created_at->diffForHumans() }}</small>
                    </div>
                @endforeach
            </div>
            <form wire:submit.prevent="send" class="mt-4">
                <textarea wire:model="content" class="w-full p-2 border rounded" placeholder="Type a message..."></textarea>
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded mt-2">Send</button>
            </form>
        @else
            <p class="text-center text-gray-500 mt-4">Select a conversation to start messaging</p>
        @endif
    </div>
</div>
