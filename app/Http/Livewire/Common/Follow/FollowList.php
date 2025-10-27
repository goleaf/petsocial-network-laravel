<?php

namespace App\Http\Livewire\Common\Follow;

use App\Models\User;
use Illuminate\Support\Facades\Schema;
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
            // Wrap the name and username filters in a closure so pagination respects grouped conditions.
            $followers = User::query()
                ->where(function ($query): void {
                    $query->where('name', 'like', '%'.$this->search.'%');

                    if (Schema::hasColumn('users', 'username')) {
                        $query->orWhere('username', 'like', '%'.$this->search.'%');
                    }
                })
                ->paginate($this->perPage);
        }

        return view('livewire.common.follow.follow-list', [
            'followers' => $followers
        ]);
    }
}
