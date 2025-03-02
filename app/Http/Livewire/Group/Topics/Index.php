<?php

namespace App\Http\Livewire\Group\Topics;

use App\Models\Group\Group;
use App\Models\Group\Topic;
use App\Models\Poll;
use App\Models\PollOption;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;

class Index extends Component
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
    public $pollDuration = 7; // days
    public $pollMultipleChoice = false;
    
    // Filtering and searching
    public $search = '';
    public $filter = 'all';
    public $sortBy = 'latest';
    
    // Bulk actions
    public $selectedTopics = [];
    public $bulkAction;
    
    // Reporting
    public $reportReason;
    public $reportTopicId;
    
    protected $listeners = ['refreshTopics' => '$refresh'];
    
    public function mount(Group $group)
    {
        $this->group = $group;
        $this->resetPollOptions();
    }
    
    public function resetPollOptions()
    {
        $this->pollOptions = [
            ['text' => ''],
            ['text' => ''],
        ];
    }
    
    public function addPollOption()
    {
        $this->pollOptions[] = ['text' => ''];
    }
    
    public function removePollOption($index)
    {
        if (count($this->pollOptions) > 2) {
            unset($this->pollOptions[$index]);
            $this->pollOptions = array_values($this->pollOptions);
        }
    }
    
    public function createTopic()
    {
        $this->validate([
            'title' => 'required|string|min:3|max:100',
            'content' => 'required|string',
            'attachments.*' => 'nullable|file|max:10240',
            'pollQuestion' => $this->includePoll ? 'required|string|max:255' : 'nullable',
            'pollOptions.*.text' => $this->includePoll ? 'required|string|max:100' : 'nullable',
            'pollDuration' => $this->includePoll ? 'required|integer|min:1|max:90' : 'nullable',
        ]);
        
        $topic = $this->group->topics()->create([
            'title' => $this->title,
            'content' => $this->content,
            'user_id' => auth()->id(),
        ]);
        
        // Handle attachments
        if (!empty($this->attachments)) {
            foreach ($this->attachments as $attachment) {
                $path = $attachment->store('topic-attachments', 'public');
                $topic->attachments()->create([
                    'path' => $path,
                    'filename' => $attachment->getClientOriginalName(),
                    'mime_type' => $attachment->getMimeType(),
                    'size' => $attachment->getSize(),
                ]);
            }
        }
        
        // Create poll if included
        if ($this->includePoll) {
            $poll = $topic->poll()->create([
                'question' => $this->pollQuestion,
                'multiple_choice' => $this->pollMultipleChoice,
                'expires_at' => now()->addDays($this->pollDuration),
            ]);
            
            foreach ($this->pollOptions as $option) {
                if (!empty($option['text'])) {
                    $poll->options()->create([
                        'text' => $option['text'],
                    ]);
                }
            }
        }
        
        $this->resetForm();
        $this->showCreateModal = false;
        session()->flash('message', 'Topic created successfully!');
    }
    
    public function resetForm()
    {
        $this->title = '';
        $this->content = '';
        $this->attachments = [];
        $this->includePoll = false;
        $this->pollQuestion = '';
        $this->resetPollOptions();
        $this->pollDuration = 7;
        $this->pollMultipleChoice = false;
        $this->selectedTopicId = null;
    }
    
    public function editTopic($topicId)
    {
        $this->selectedTopicId = $topicId;
        $topic = Topic::findOrFail($topicId);
        
        $this->title = $topic->title;
        $this->content = $topic->content;
        
        if ($topic->poll) {
            $this->includePoll = true;
            $this->pollQuestion = $topic->poll->question;
            $this->pollMultipleChoice = $topic->poll->multiple_choice;
            $this->pollDuration = now()->diffInDays($topic->poll->expires_at);
            
            $this->pollOptions = [];
            foreach ($topic->poll->options as $option) {
                $this->pollOptions[] = ['text' => $option->text];
            }
        }
        
        $this->showCreateModal = true;
    }
    
    public function updateTopic()
    {
        $this->validate([
            'title' => 'required|string|min:3|max:100',
            'content' => 'required|string',
            'attachments.*' => 'nullable|file|max:10240',
            'pollQuestion' => $this->includePoll ? 'required|string|max:255' : 'nullable',
            'pollOptions.*.text' => $this->includePoll ? 'required|string|max:100' : 'nullable',
            'pollDuration' => $this->includePoll ? 'required|integer|min:1|max:90' : 'nullable',
        ]);
        
        $topic = Topic::findOrFail($this->selectedTopicId);
        
        $topic->update([
            'title' => $this->title,
            'content' => $this->content,
        ]);
        
        // Handle attachments
        if (!empty($this->attachments)) {
            foreach ($this->attachments as $attachment) {
                $path = $attachment->store('topic-attachments', 'public');
                $topic->attachments()->create([
                    'path' => $path,
                    'filename' => $attachment->getClientOriginalName(),
                    'mime_type' => $attachment->getMimeType(),
                    'size' => $attachment->getSize(),
                ]);
            }
        }
        
        // Update poll
        if ($this->includePoll) {
            if ($topic->poll) {
                $topic->poll->update([
                    'question' => $this->pollQuestion,
                    'multiple_choice' => $this->pollMultipleChoice,
                    'expires_at' => now()->addDays($this->pollDuration),
                ]);
                
                // Delete existing options and create new ones
                $topic->poll->options()->delete();
            } else {
                $poll = $topic->poll()->create([
                    'question' => $this->pollQuestion,
                    'multiple_choice' => $this->pollMultipleChoice,
                    'expires_at' => now()->addDays($this->pollDuration),
                ]);
            }
            
            $poll = $topic->poll;
            foreach ($this->pollOptions as $option) {
                if (!empty($option['text'])) {
                    $poll->options()->create([
                        'text' => $option['text'],
                    ]);
                }
            }
        } else if ($topic->poll) {
            // Remove poll if it exists but is no longer included
            $topic->poll->options()->delete();
            $topic->poll->delete();
        }
        
        $this->resetForm();
        $this->showCreateModal = false;
        session()->flash('message', 'Topic updated successfully!');
    }
    
    public function deleteTopic($topicId)
    {
        $topic = Topic::findOrFail($topicId);
        
        // Check if user is authorized to delete
        if (auth()->id() === $topic->user_id || 
            $this->group->members()->where('user_id', auth()->id())->where('role', 'admin')->exists() ||
            $this->group->members()->where('user_id', auth()->id())->where('role', 'moderator')->exists()) {
            
            // Delete poll if exists
            if ($topic->poll) {
                $topic->poll->options()->delete();
                $topic->poll->delete();
            }
            
            // Delete attachments
            foreach ($topic->attachments as $attachment) {
                \Storage::disk('public')->delete($attachment->path);
                $attachment->delete();
            }
            
            // Delete comments
            $topic->comments()->delete();
            
            // Delete topic
            $topic->delete();
            
            session()->flash('message', 'Topic deleted successfully!');
        } else {
            session()->flash('error', 'You are not authorized to delete this topic.');
        }
    }
    
    public function pinTopic($topicId)
    {
        $topic = Topic::findOrFail($topicId);
        $topic->update(['is_pinned' => !$topic->is_pinned]);
    }
    
    public function reportTopic($topicId)
    {
        $this->reportTopicId = $topicId;
        $this->showReportModal = true;
    }
    
    public function submitReport()
    {
        $this->validate([
            'reportReason' => 'required|string|min:10|max:500',
        ]);
        
        $topic = Topic::findOrFail($this->reportTopicId);
        
        $topic->reports()->create([
            'user_id' => auth()->id(),
            'reason' => $this->reportReason,
        ]);
        
        $this->reportReason = '';
        $this->showReportModal = false;
        session()->flash('message', 'Topic reported successfully.');
    }
    
    public function toggleBulkSelect($topicId)
    {
        if (in_array($topicId, $this->selectedTopics)) {
            $this->selectedTopics = array_diff($this->selectedTopics, [$topicId]);
        } else {
            $this->selectedTopics[] = $topicId;
        }
    }
    
    public function executeBulkAction()
    {
        if (empty($this->selectedTopics)) {
            session()->flash('error', 'No topics selected.');
            return;
        }
        
        switch ($this->bulkAction) {
            case 'delete':
                foreach ($this->selectedTopics as $topicId) {
                    $this->deleteTopic($topicId);
                }
                break;
            case 'pin':
                foreach ($this->selectedTopics as $topicId) {
                    $topic = Topic::findOrFail($topicId);
                    $topic->update(['is_pinned' => true]);
                }
                break;
            case 'unpin':
                foreach ($this->selectedTopics as $topicId) {
                    $topic = Topic::findOrFail($topicId);
                    $topic->update(['is_pinned' => false]);
                }
                break;
        }
        
        $this->selectedTopics = [];
        $this->showBulkActionModal = false;
    }
    
    public function render()
    {
        $query = $this->group->topics();
        
        if ($this->search) {
            $query->where(function($q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                  ->orWhere('content', 'like', '%' . $this->search . '%');
            });
        }
        
        switch ($this->filter) {
            case 'mine':
                $query->where('user_id', auth()->id());
                break;
            case 'pinned':
                $query->where('is_pinned', true);
                break;
            case 'polls':
                $query->whereHas('poll');
                break;
        }
        
        switch ($this->sortBy) {
            case 'latest':
                $query->latest();
                break;
            case 'oldest':
                $query->oldest();
                break;
            case 'most_commented':
                $query->withCount('comments')->orderBy('comments_count', 'desc');
                break;
            case 'most_liked':
                $query->withCount('likes')->orderBy('likes_count', 'desc');
                break;
        }
        
        // Always show pinned topics first
        $pinnedTopics = $this->group->topics()->where('is_pinned', true)->get();
        $regularTopics = $query->where('is_pinned', false)->paginate(10);
        
        return view('livewire.group.topics.index', [
            'pinnedTopics' => $pinnedTopics,
            'regularTopics' => $regularTopics,
        ])->layout('layouts.app');
    }
}
