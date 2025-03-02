<?php

namespace App\Http\Livewire\Content;

use Livewire\Component;

class ReportPost extends Component
{
    public $postId;
    public $reason;
    public $reported = false;

    public function mount($postId)
    {
        $this->postId = $postId;
        $this->reported = PostReport::where('user_id', auth()->id())->where('post_id', $postId)->exists();
    }

    public function report()
    {
        $this->validate(['reason' => 'required|max:255']);
        PostReport::create([
            'user_id' => auth()->id(),
            'post_id' => $this->postId,
            'reason' => $this->reason,
        ]);
        $this->reported = true;
        $this->reason = '';
    }

    public function render()
    {
        return view('livewire.report-post');
    }
}
