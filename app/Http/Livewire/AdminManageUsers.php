<?php

namespace App\Http\Livewire;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;

use Livewire\Component;

class AdminManageUsers extends Component
{
    public $users;
    public $reportedPosts;
    public $reportedComments;

    public function mount()
    {
        $this->loadUsers();
        $this->loadReports();
    }

    public function loadUsers()
    {
        $this->users = User::where('id', '!=', auth()->id())->get();
    }

    public function loadReports()
    {
        $this->reportedPosts = Post::whereHas('reports')->with('reports')->get();
        $this->reportedComments = Comment::whereHas('reports')->with('reports')->get();
    }

    public function deleteUser($userId)
    {
        User::find($userId)->delete();
        $this->loadUsers();
        $this->loadReports();
    }

    public function deletePost($postId)
    {
        Post::find($postId)->delete();
        $this->loadReports();
    }

    public function deleteComment($commentId)
    {
        Comment::find($commentId)->delete();
        $this->loadReports();
    }

    public function render()
    {
        return view('livewire.admin-manage-users')->layout('layouts.app');
    }
}
