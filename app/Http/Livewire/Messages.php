<?php

namespace App\Http\Livewire;

use App\Models\Message;
use App\Models\User;
use Livewire\Component;

class Messages extends Component
{
    public $receiverId;
    public $content;
    public $conversations;
    public $messages;

    public function mount()
    {
        $this->loadConversations();
    }

    public function loadConversations()
    {
        $this->conversations = User::whereIn('id', function ($query) {
            $query->select('sender_id')->from('messages')->where('receiver_id', auth()->id())
                ->union(
                    DB::table('messages')->select('receiver_id')->where('sender_id', auth()->id())
                );
        })->get();

        if ($this->receiverId) {
            $this->loadMessages();
        }
    }

    public function loadMessages()
    {
        $this->messages = Message::where(function ($query) {
            $query->where('sender_id', auth()->id())->where('receiver_id', $this->receiverId);
        })->orWhere(function ($query) {
            $query->where('sender_id', $this->receiverId)->where('receiver_id', auth()->id());
        })->orderBy('created_at', 'asc')->get();

        // Mark messages as read
        Message::where('sender_id', $this->receiverId)
            ->where('receiver_id', auth()->id())
            ->where('read', false)
            ->update(['read' => true]);
    }

    public function send()
    {
        $this->validate(['content' => 'required|max:1000', 'receiverId' => 'required|exists:users,id']);
        Message::create([
            'sender_id' => auth()->id(),
            'receiver_id' => $this->receiverId,
            'content' => $this->content,
        ]);
        $this->content = '';
        $this->loadMessages();
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
