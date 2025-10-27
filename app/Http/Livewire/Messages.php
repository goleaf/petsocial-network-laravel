<?php

namespace App\Http\Livewire;

use App\Events\MessageRead;
use App\Events\MessageSent;
use App\Models\Message;
use Livewire\Component;

class Messages extends Component
{
    public $receiverId;

    public $content;

    public $conversations = [];

    // Initialize the messages array so Alpine/Livewire bindings always have a predictable structure.
    public $messages = [];

    protected $listeners = ['messageReceived' => 'loadMessages'];

    public function mount()
    {
        $this->loadConversations();
    }

    public function loadConversations()
    {
        // Normalise the conversation list to a collection so the view always iterates safely.
        $this->conversations = collect(auth()->user()->friends ?? [])->values();

        if ($this->receiverId) {
            $this->loadMessages();
        }
    }

    public function loadMessages()
    {
        // Fetch the full conversation between the authenticated user and the selected friend.
        $messages = Message::where(function ($query) {
            $query->where('sender_id', auth()->id())->where('receiver_id', $this->receiverId);
        })->orWhere(function ($query) {
            $query->where('sender_id', $this->receiverId)->where('receiver_id', auth()->id());
        })->orderBy('created_at', 'asc')->get();

        // Identify the unread messages sent by the other user so we can mark them as read.
        $unreadMessages = $messages->filter(function (Message $message) {
            return $message->sender_id === $this->receiverId &&
                $message->receiver_id === auth()->id() &&
                $message->read === false;
        });

        if ($unreadMessages->isNotEmpty()) {
            // Extract the IDs before updating so we can notify the sender.
            $messageIds = $unreadMessages->pluck('id')->all();

            // Persist the read state for the identified messages.
            Message::whereIn('id', $messageIds)->update(['read' => true]);

            // Update the in-memory collection to keep the UI consistent with the database.
            $messages = $messages->map(function (Message $message) use ($messageIds) {
                if (in_array($message->id, $messageIds, true)) {
                    $message->read = true;
                }

                return $message;
            });

            // Inform the original sender that their messages were read for instant read receipts.
            broadcast(new MessageRead($messageIds, auth()->id(), $this->receiverId));
        }

        // Store a simplified array so Alpine.js can reactively render the conversation thread.
        $this->messages = $messages->map(function (Message $message) {
            return [
                'id' => $message->id,
                'sender_id' => $message->sender_id,
                'receiver_id' => $message->receiver_id,
                'content' => $message->content,
                'created_at' => $message->created_at->toISOString(),
                'read' => $message->read,
            ];
        })->toArray();
    }

    public function send()
    {
        $this->validate(['content' => 'required|max:1000', 'receiverId' => 'required|exists:users,id']);
        $message = Message::create([
            'sender_id' => auth()->id(),
            'receiver_id' => $this->receiverId,
            'content' => $this->content,
            // Ensure all new outbound messages start unread so we can track receipts accurately.
            'read' => false,
        ]);
        event(new MessageSent($message));
        $this->content = '';
        $this->loadMessages();
    }

    /**
     * Mark the provided messages as read and broadcast receipts back to the sender.
     */
    public function markMessagesAsRead(array $messageIds): void
    {
        // Normalise the incoming IDs to a unique integer list for querying.
        $idsToAcknowledge = collect($messageIds)
            ->filter(static fn ($id) => is_numeric($id))
            ->map(static fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($idsToAcknowledge->isEmpty()) {
            return;
        }

        $messages = Message::whereIn('id', $idsToAcknowledge)
            ->where('receiver_id', auth()->id())
            ->where('sender_id', $this->receiverId)
            ->where('read', false)
            ->get();

        if ($messages->isEmpty()) {
            return;
        }

        $messageIdsToUpdate = $messages->pluck('id')->all();

        // Persist the read flag so the acknowledgement survives future refreshes.
        Message::whereIn('id', $messageIdsToUpdate)->update(['read' => true]);

        // Update the Livewire state so the current user sees receipts instantly.
        $this->messages = collect($this->messages)
            ->map(function (array $message) use ($messageIdsToUpdate) {
                if (in_array($message['id'], $messageIdsToUpdate, true)) {
                    $message['read'] = true;
                }

                return $message;
            })
            ->toArray();

        // Notify the sender that their pending messages were just read.
        broadcast(new MessageRead($messageIdsToUpdate, auth()->id(), $this->receiverId));
    }

    public function selectConversation($userId)
    {
        $this->receiverId = $userId;
        $this->loadMessages();
    }

    public function render()
    {
        return view('livewire.messages')->layout('layouts.app');
    }
}
