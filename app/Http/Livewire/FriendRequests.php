<?php

namespace App\Http\Livewire;

use Livewire\Component;

class FriendRequests extends Component
{
    public $pendingRequests;

    public function mount()
    {
        $this->loadRequests();
    }

    public function loadRequests()
    {
        $this->pendingRequests = auth()->user()->pendingReceivedRequests()->with('sender')->get();
    }

    public function accept($requestId)
    {
        $request = FriendRequest::find($requestId);
        if ($request && $request->receiver_id === auth()->id()) {
            $request->update(['status' => 'accepted']);
            $this->loadRequests();
        }
    }

    public function decline($requestId)
    {
        $request = FriendRequest::find($requestId);
        if ($request && $request->receiver_id === auth()->id()) {
            $request->update(['status' => 'declined']);
            $this->loadRequests();
        }
    }

    public function render()
    {
        return view('livewire.friend-requests')->layout('layouts.app');
    }
}
