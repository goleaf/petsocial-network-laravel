<?php

namespace App\Http\Livewire\Content;

use Livewire\Component;

class ReportComment extends Component
{
    public $commentId;
    public $reason;
    public $reported = false;

    public function mount($commentId)
    {
        $this->commentId = $commentId;
        $this->reported = CommentReport::where('user_id', auth()->id())->where('comment_id', $commentId)->exists();
    }

    public function report()
    {
        $this->validate(['reason' => 'required|max:255']);
        CommentReport::create([
            'user_id' => auth()->id(),
            'comment_id' => $this->commentId,
            'reason' => $this->reason,
        ]);
        $this->reported = true;
        $this->reason = '';
    }

    public function render()
    {
        return view('livewire.report-comment');
    }
}
