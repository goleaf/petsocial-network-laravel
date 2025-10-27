<?php

namespace App\Http\Livewire\Common\Follow;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class FollowList extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 12;

    protected $queryString = [
        'search' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $followers = collect();

        if ($this->search) {
            $followers = User::where('name', 'like', "%{$this->search}%")
                ->orWhere('username', 'like', "%{$this->search}%")
                ->paginate($this->perPage);
        }

        return view('livewire.common.follow.follow-list', [
            'followers' => $followers
        ]);
    }
}
