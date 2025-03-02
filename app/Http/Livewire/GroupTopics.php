<?php

namespace App\Http\Livewire;

use App\Models\Group;
use App\Models\GroupTopic;
use App\Models\Poll;
use App\Models\PollOption;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;

class GroupTopics extends Component
{
    use WithPagination, WithFileUploads;
    
    public Group $group;
    public $showCreateModal = false;
    public $showReportModal = false;
    public $showBulkActionModal = false;
    
    // Topic creation/editing
    public $title;
    public $content;
    public $attachments = [];
    public $selectedTopicId;
    
    // Poll creation
    public $includePoll = false;
    public $pollQuestion;
    public $pollOptions = [];
    public $pollExpiresAt;
    public $pollAllowMultiple = false;
    public $pollIsAnonymous = false;
    
    // Filtering and search
    public $search = '';
    public $filter = 'all';
    public $sort = 'latest';
    
    // Bulk actions
    public $selectedTopics = [];
    public $bulkAction;
    
    // Reporting
    public $reportReason;
    public $reportTopicId;
    
    protected $listeners = ['refresh' => '$refresh'];
    
    public function mount(Group $group)
    {
        $this->group = $group;
        $this->addPollOption();
        $this->addPollOption();
    }
    
    public function createTopic()
    {
        $this->validate([
            'title' => 'required|string|min:3|max:255',
            'content' => 'required|string',
            'attachments.*' => 'nullable|file|max:10240', // 10MB max
            'pollQuestion' => 'required_if:includePoll,true|nullable|string|max:255',
            'pollOptions.*' => 'required_if:includePoll,true|nullable|string|max:100',
            'pollExpiresAt' => 'nullable|date|after:now',
        ]);
        
        $topic = GroupTopic::create([
            'title' => $this->title,
            'content' => $this->content,
            'group_id' => $this->group->id,
            'user_id' => auth()->id(),
            'last_activity_at' => now(),
        ]);
        
        // Handle attachments
        foreach ($this->attachments as $attachment) {
            $path = $attachment->store('topic-attachments', 'public');
            
            $topic->attachments()->create([
                'user_id' => auth()->id(),
                'file_path' => $path,
                'file_name' => $attachment->getClientOriginalName(),
                'file_size' => $attachment->getSize(),
                'file_type' => $attachment->getMimeType(),
            ]);
        }
        
        // Create poll if enabled
        if ($this->includePoll) {
            $poll = Poll::create([
                'question' => $this->pollQuestion,
                'group_topic_id' => $topic->id,
                'user_id' => auth()->id(),
                'expires_at' => $this->pollExpiresAt,
                'allow_multiple' => $this->pollAllowMultiple,
                'is_anonymous' => $this->pollIsAnonymous,
            ]);
            
            foreach ($this->pollOptions as $option) {
                if (!empty($option)) {
                    PollOption::create([
                        'poll_id' => $poll->id,
                        'text' => $option,
                    ]);
                }
            }
        }
        
        $this->reset([
            'title', 'content', 'attachments', 'includePoll', 
            'pollQuestion', 'pollOptions', 'pollExpiresAt', 
            'pollAllowMultiple', 'pollIsAnonymous'
        ]);
        
        $this->addPollOption();
        $this->addPollOption();
        
        $this->showCreateModal = false;
        
        session()->flash('message', 'Topic created successfully!');
        $this->emit('topicCreated');
    }
    
    public function editTopic($topicId)
    {
        $topic = GroupTopic::findOrFail($topicId);
        
        // Check permissions
        if (!$this->canModifyTopic($topic)) {
            session()->flash('error', 'You do not have permission to edit this topic.');
            return;
        }
        
        $this->selectedTopicId = $topic->id;
        $this->title = $topic->title;
        $this->content = $topic->content;
        
        if ($topic->hasPoll()) {
            $this->includePoll = true;
            $this->pollQuestion = $topic->poll->question;
            $this->pollOptions = $topic->poll->options->pluck('text')->toArray();
            $this->pollExpiresAt = $topic->poll->expires_at;
            $this->pollAllowMultiple = $topic->poll->allow_multiple;
            $this->pollIsAnonymous = $topic->poll->is_anonymous;
        }
        
        $this->showCreateModal = true;
    }
    
    public function updateTopic()
    {
        $this->validate([
            'title' => 'required|string|min:3|max:255',
            'content' => 'required|string',
            'attachments.*' => 'nullable|file|max:10240', // 10MB max
            'pollQuestion' => 'required_if:includePoll,true|nullable|string|max:255',
            'pollOptions.*' => 'required_if:includePoll,true|nullable|string|max:100',
            'pollExpiresAt' => 'nullable|date|after:now',
        ]);
        
        $topic = GroupTopic::findOrFail($this->selectedTopicId);
        
        // Check permissions
        if (!$this->canModifyTopic($topic)) {
            session()->flash('error', 'You do not have permission to edit this topic.');
            return;
        }
        
        $topic->update([
            'title' => $this->title,
            'content' => $this->content,
        ]);
        
        // Handle attachments
        foreach ($this->attachments as $attachment) {
            $path = $attachment->store('topic-attachments', 'public');
            
            $topic->attachments()->create([
                'user_id' => auth()->id(),
                'file_path' => $path,
                'file_name' => $attachment->getClientOriginalName(),
                'file_size' => $attachment->getSize(),
                'file_type' => $attachment->getMimeType(),
            ]);
        }
        
        // Update or create poll
        if ($this->includePoll) {
            $poll = $topic->poll ?? Poll::create([
                'group_topic_id' => $topic->id,
                'user_id' => auth()->id(),
            ]);
            
            $poll->update([
                'question' => $this->pollQuestion,
                'expires_at' => $this->pollExpiresAt,
                'allow_multiple' => $this->pollAllowMultiple,
                'is_anonymous' => $this->pollIsAnonymous,
            ]);
            
            // Delete existing options
            $poll->options()->delete();
            
            // Create new options
            foreach ($this->pollOptions as $option) {
                if (!empty($option)) {
                    PollOption::create([
                        'poll_id' => $poll->id,
                        'text' => $option,
                    ]);
                }
            }
        } elseif ($topic->hasPoll()) {
            // Remove poll if it was disabled
            $topic->poll->options()->delete();
            $topic->poll->delete();
        }
        
        $this->reset([
            'selectedTopicId', 'title', 'content', 'attachments', 'includePoll', 
            'pollQuestion', 'pollOptions', 'pollExpiresAt', 
            'pollAllowMultiple', 'pollIsAnonymous'
        ]);
        
        $this->addPollOption();
        $this->addPollOption();
        
        $this->showCreateModal = false;
        
        session()->flash('message', 'Topic updated successfully!');
        $this->emit('refresh');
    }
    
    public function addPollOption()
    {
        $this->pollOptions[] = '';
    }
    
    public function removePollOption($index)
    {
        unset($this->pollOptions[$index]);
        $this->pollOptions = array_values($this->pollOptions);
    }
    
    public function pinTopic($topicId)
    {
        $topic = GroupTopic::findOrFail($topicId);
        
        if (!$this->canModerate()) {
            session()->flash('error', 'You do not have permission to pin topics.');
            return;
        }
        
        $topic->update(['is_pinned' => !$topic->is_pinned]);
        
        $action = $topic->is_pinned ? 'pinned' : 'unpinned';
        session()->flash('message', "Topic has been {$action}.");
        $this->emit('refresh');
    }
    
    public function lockTopic($topicId)
    {
        $topic = GroupTopic::findOrFail($topicId);
        
        if (!$this->canModerate()) {
            session()->flash('error', 'You do not have permission to lock topics.');
            return;
        }
        
        $topic->update(['is_locked' => !$topic->is_locked]);
        
        $action = $topic->is_locked ? 'locked' : 'unlocked';
        session()->flash('message', "Topic has been {$action}.");
        $this->emit('refresh');
    }
    
    public function deleteTopic($topicId)
    {
        $topic = GroupTopic::findOrFail($topicId);
        
        if (!$this->canDeleteTopic($topic)) {
            session()->flash('error', 'You do not have permission to delete this topic.');
            return;
        }
        
        // Delete attachments, poll, and replies
        $topic->attachments()->delete();
        
        if ($topic->hasPoll()) {
            $topic->poll->options()->delete();
            $topic->poll->delete();
        }
        
        $topic->replies()->delete();
        $topic->delete();
        
        session()->flash('message', 'Topic has been deleted.');
        $this->emit('refresh');
    }
    
    public function openReportModal($topicId)
    {
        $this->reportTopicId = $topicId;
        $this->showReportModal = true;
    }
    
    public function submitReport()
    {
        $this->validate([
            'reportReason' => 'required|string|min:10|max:500',
        ]);
        
        // In a real implementation, you would create a report record in the database
        
        $this->reset(['reportReason', 'reportTopicId']);
        $this->showReportModal = false;
        
        session()->flash('message', 'Your report has been submitted. Thank you for helping keep the community safe.');
    }
    
    public function executeBulkAction()
    {
        if (empty($this->selectedTopics)) {
            session()->flash('error', 'No topics selected.');
            return;
        }
        
        if (!$this->canModerate()) {
            session()->flash('error', 'You do not have permission to perform bulk actions.');
            return;
        }
        
        switch ($this->bulkAction) {
            case 'delete':
                GroupTopic::whereIn('id', $this->selectedTopics)->delete();
                session()->flash('message', count($this->selectedTopics) . ' topics have been deleted.');
                break;
                
            case 'pin':
                GroupTopic::whereIn('id', $this->selectedTopics)->update(['is_pinned' => true]);
                session()->flash('message', count($this->selectedTopics) . ' topics have been pinned.');
                break;
                
            case 'unpin':
                GroupTopic::whereIn('id', $this->selectedTopics)->update(['is_pinned' => false]);
                session()->flash('message', count($this->selectedTopics) . ' topics have been unpinned.');
                break;
                
            case 'lock':
                GroupTopic::whereIn('id', $this->selectedTopics)->update(['is_locked' => true]);
                session()->flash('message', count($this->selectedTopics) . ' topics have been locked.');
                break;
                
            case 'unlock':
                GroupTopic::whereIn('id', $this->selectedTopics)->update(['is_locked' => false]);
                session()->flash('message', count($this->selectedTopics) . ' topics have been unlocked.');
                break;
        }
        
        $this->reset(['selectedTopics', 'bulkAction']);
        $this->showBulkActionModal = false;
        $this->emit('refresh');
    }
    
    public function canModerate()
    {
        return $this->group->isAdmin(auth()->user()) || $this->group->isModerator(auth()->user());
    }
    
    public function canModifyTopic($topic)
    {
        return $topic->user_id === auth()->id() || $this->canModerate();
    }
    
    public function canDeleteTopic($topic)
    {
        return $topic->user_id === auth()->id() || $this->canModerate();
    }
    
    public function render()
    {
        $query = $this->group->topics();
        
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('title', 'like', "%{$this->search}%")
                  ->orWhere('content', 'like', "%{$this->search}%");
            });
        }
        
        switch ($this->filter) {
            case 'pinned':
                $query->where('is_pinned', true);
                break;
            case 'locked':
                $query->where('is_locked', true);
                break;
            case 'my':
                $query->where('user_id', auth()->id());
                break;
        }
        
        switch ($this->sort) {
            case 'latest':
                $query->latest();
                break;
            case 'oldest':
                $query->oldest();
                break;
            case 'active':
                $query->orderBy('last_activity_at', 'desc');
                break;
            case 'views':
                $query->orderBy('views_count', 'desc');
                break;
        }
        
        // Always show pinned topics first
        $pinnedTopics = clone $query;
        $pinnedTopics->where('is_pinned', true);
        
        if ($this->filter !== 'pinned') {
            $query->where('is_pinned', false);
        }
        
        $topics = $query->paginate(10);
        $pinnedTopics = $this->filter === 'pinned' ? collect() : $pinnedTopics->get();
        
        return view('livewire.group-topics', [
            'topics' => $topics,
            'pinnedTopics' => $pinnedTopics,
            'canModerate' => $this->canModerate(),
        ])->layout('layouts.app');
    }
}
