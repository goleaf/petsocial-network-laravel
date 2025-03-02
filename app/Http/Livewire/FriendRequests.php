<?php

namespace App\Http\Livewire;

use App\Models\Friendship;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class FriendRequests extends Component
{
    use WithPagination;
    
    public $search = '';
    public $showSent = false;
    
    protected $listeners = ['refresh' => '$refresh'];
    
    public function toggleView()
    {
        $this->showSent = !$this->showSent;
        $this->resetPage();
    }
    
    public function accept($friendshipId)
    {
        $friendship = Friendship::find($friendshipId);
        
        if ($friendship && $friendship->recipient_id === auth()->id()) {
            $friendship->accept();
            $this->emit('refresh');
            session()->flash('message', 'Friend request accepted!');
        }
    }
    
    public function decline($friendshipId)
    {
        $friendship = Friendship::find($friendshipId);
        
        if ($friendship && $friendship->recipient_id === auth()->id()) {
            $friendship->decline();
            $this->emit('refresh');
            session()->flash('message', 'Friend request declined.');
        }
    }
    
    public function cancelRequest($friendshipId)
    {
        $friendship = Friendship::find($friendshipId);
        
        if ($friendship && $friendship->sender_id === auth()->id() && $friendship->status === 'pending') {
            $friendship->delete();
            $this->emit('refresh');
            session()->flash('message', 'Friend request canceled.');
        }
    }
    
    public function render()
    {
        $query = $this->showSent 
            ? auth()->user()->sentFriendships()->pending()->with('recipient')
            : auth()->user()->receivedFriendships()->pending()->with('sender');
            
        if ($this->search) {
            $query->when($this->showSent, function ($q) {
                $q->whereHas('recipient', function ($q) {
                    $q->where('name', 'like', "%{$this->search}%");
                });
            }, function ($q) {
                $q->whereHas('sender', function ($q) {
                    $q->where('name', 'like', "%{$this->search}%");
                });
            });
        }
        
        $friendships = $query->latest()->paginate(10);
        
        return view('livewire.friend-requests', [
            'friendships' => $friendships,
        ])->layout('layouts.app');
    }
}
