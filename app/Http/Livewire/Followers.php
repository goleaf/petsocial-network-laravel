<?php

namespace App\Http\Livewire;

use App\Models\Follow;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class Followers extends Component
{
    use WithPagination;

    public $search = '';
    public $tab = 'followers'; // 'followers' or 'following'
    public $selectedUsers = [];
    
    protected $listeners = ['refresh' => '$refresh'];
    
    public function setTab($tab)
    {
        $this->tab = $tab;
        $this->resetPage();
    }
    
    public function follow($userId)
    {
        $user = User::find($userId);
        
        if ($user && !auth()->user()->isFollowing($user)) {
            Follow::create([
                'follower_id' => auth()->id(),
                'followed_id' => $userId,
                'notify' => true,
            ]);
            
            // Create a notification for the followed user
            $user->notifications()->create([
                'type' => 'new_follower',
                'notifiable_type' => User::class,
                'notifiable_id' => auth()->id(),
                'data' => [
                    'message' => auth()->user()->name . ' started following you',
                ],
                'priority' => 'normal',
            ]);
            
            $this->emit('refresh');
            session()->flash('message', 'You are now following ' . $user->name);
        }
    }
    
    public function unfollow($userId)
    {
        Follow::where('follower_id', auth()->id())
              ->where('followed_id', $userId)
              ->delete();
              
        $this->emit('refresh');
        session()->flash('message', 'You have unfollowed this user');
    }
    
    public function removeFollower($userId)
    {
        Follow::where('follower_id', $userId)
              ->where('followed_id', auth()->id())
              ->delete();
              
        $this->emit('refresh');
        session()->flash('message', 'Follower has been removed');
    }
    
    public function toggleNotifications($userId)
    {
        $follow = Follow::where('follower_id', auth()->id())
                        ->where('followed_id', $userId)
                        ->first();
                        
        if ($follow) {
            $follow->update(['notify' => !$follow->notify]);
            
            $status = $follow->notify ? 'enabled' : 'disabled';
            session()->flash('message', "Notifications for this user have been {$status}");
            $this->emit('refresh');
        }
    }
    
    public function bulkUnfollow()
    {
        if (empty($this->selectedUsers)) {
            session()->flash('error', 'No users selected');
            return;
        }
        
        Follow::where('follower_id', auth()->id())
              ->whereIn('followed_id', $this->selectedUsers)
              ->delete();
              
        $this->selectedUsers = [];
        $this->emit('refresh');
        session()->flash('message', 'Selected users have been unfollowed');
    }
    
    public function bulkRemoveFollowers()
    {
        if (empty($this->selectedUsers)) {
            session()->flash('error', 'No users selected');
            return;
        }
        
        Follow::whereIn('follower_id', $this->selectedUsers)
              ->where('followed_id', auth()->id())
              ->delete();
              
        $this->selectedUsers = [];
        $this->emit('refresh');
        session()->flash('message', 'Selected followers have been removed');
    }

    public function render()
    {
        if ($this->tab === 'followers') {
            $users = auth()->user()->followers()
                ->when($this->search, function ($query) {
                    return $query->where('name', 'like', "%{$this->search}%");
                })
                ->withPivot('notify')
                ->paginate(10);
        } else {
            $users = auth()->user()->following()
                ->when($this->search, function ($query) {
                    return $query->where('name', 'like', "%{$this->search}%");
                })
                ->withPivot('notify')
                ->paginate(10);
        }

        return view('livewire.followers', [
            'users' => $users,
        ])->layout('layouts.app');
    }
}
