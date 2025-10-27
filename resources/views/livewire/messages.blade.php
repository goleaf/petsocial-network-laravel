<div class="flex flex-col lg:flex-row">
    <div class="w-full lg:w-1/4 bg-white p-4 rounded-lg shadow mb-4 lg:mb-0">
        <h2 class="text-xl font-semibold mb-4">Conversations</h2>
        @foreach ($conversations as $user)
            <div class="p-2 hover:bg-gray-100 cursor-pointer" wire:click="selectConversation({{ $user->id }})">
                {{ $user->name }}
            </div>
        @endforeach
    </div>
    <div class="w-full lg:w-3/4 lg:ml-4">
        @if ($receiverId)
            <div
                class="bg-white p-4 rounded-lg shadow h-96 overflow-y-auto"
                x-data="{ messages: @entangle('messages') }"
                x-init="
                    // Subscribe to the authenticated user's chat channel to receive live updates.
                    Echo.channel('chat.' + {{ auth()->id() }})
                        .listen('MessageSent', (e) => {
                            // Only append messages that belong to the currently open conversation.
                            if (e.receiver_id === {{ auth()->id() }} && e.sender_id === {{ $receiverId }}) {
                                e.read = e.read ?? false;
                                messages.push(e);
                            }
                        })
                        .listen('MessageRead', (e) => {
                            // Toggle the read state for any of the user's sent messages that were just acknowledged.
                            messages = messages.map((message) => {
                                if (e.message_ids.includes(message.id)) {
                                    message.read = true;
                                }

                                return message;
                            });
                        });
                "
            >
                <template x-for="message in messages" :key="message.id">
                    <div :class="message.sender_id === {{ auth()->id() }} ? 'text-right' : 'text-left'" class="mb-2">
                        <p class="inline-block p-2 rounded-lg" :class="message.sender_id === {{ auth()->id() }} ? 'bg-blue-100' : 'bg-gray-100'">
                            <span x-text="message.content"></span>
                        </p>
                        <small class="block text-gray-400">
                            <span x-text="new Date(message.created_at).toLocaleTimeString()"></span>
                            <template x-if="message.sender_id === {{ auth()->id() }}">
                                <span>
                                    •
                                    <span x-text="message.read ? 'Read' : 'Sent'"></span>
                                </span>
                            </template>
                        </small>
                    </div>
                </template>
            </div>
            <form wire:submit.prevent="send" class="mt-4">
                <textarea wire:model="content" class="w-full p-2 border rounded" placeholder="Type a message..."></textarea>
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded mt-2 w-full sm:w-auto">Send</button>
            </form>
        @else
            <p class="text-center text-gray-500 mt-4">Select a conversation to start messaging</p>
        @endif
    </div>
</div>
