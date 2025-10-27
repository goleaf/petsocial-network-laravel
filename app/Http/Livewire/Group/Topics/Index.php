<?php

namespace App\Http\Livewire\Group\Topics;

use App\Models\Group\Group;
use App\Models\Group\Topic;
use App\Models\Poll;
use App\Models\PollOption;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

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
    public $parentTopicId = null; // Track the chosen parent so topics can be nested within threads.
    
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
            'parentTopicId' => [
                // Parent topics must belong to the same group so threads cannot span communities.
                'nullable',
                Rule::exists('group_topics', 'id')->where(function ($query) {
                    $query->where('group_id', $this->group->id);
                }),
            ],
        ]);

        $topic = $this->group->topics()->create([
            'title' => $this->title,
            'content' => $this->content,
            'user_id' => auth()->id(),
            'parent_id' => $this->parentTopicId, // Persist the parent relationship to build the nested tree.
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
        $this->parentTopicId = null; // Clearing the parent keeps new topics from inheriting stale hierarchy state.
    }
    
    public function editTopic($topicId)
    {
        $this->selectedTopicId = $topicId;
        $topic = Topic::findOrFail($topicId);

        $this->title = $topic->title;
        $this->content = $topic->content;
        $this->parentTopicId = $topic->parent_id; // Pre-populate the parent selector so editing keeps the existing hierarchy.

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
            'parentTopicId' => [
                // Ensure the parent reference remains scoped to the current group during updates.
                'nullable',
                Rule::exists('group_topics', 'id')->where(function ($query) {
                    $query->where('group_id', $this->group->id);
                }),
            ],
        ]);

        $topic = Topic::findOrFail($this->selectedTopicId);

        if ($this->parentTopicId === $this->selectedTopicId) {
            // Guard against self-referencing loops that would break recursion rendering.
            $this->addError('parentTopicId', 'A topic cannot be its own parent.');

            return;
        }

        $topic->update([
            'title' => $this->title,
            'content' => $this->content,
            'parent_id' => $this->parentTopicId, // Update the parent binding to reorganize the thread when needed.
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
        $topic = Topic::with('children')->findOrFail($topicId);

        // Check if user is authorized to delete
        if (auth()->id() === $topic->user_id ||
            $this->group->members()->where('user_id', auth()->id())->where('role', 'admin')->exists() ||
            $this->group->members()->where('user_id', auth()->id())->where('role', 'moderator')->exists()) {
            $this->deleteTopicWithRelations($topic); // Recursively purge the topic branch with all related data.

            session()->flash('message', 'Topic deleted successfully!');
        } else {
            session()->flash('error', 'You are not authorized to delete this topic.');
        }
    }

    private function deleteTopicWithRelations(Topic $topic): void
    {
        foreach ($topic->children as $childTopic) {
            // Recursively delete descendants so cascade clean-up triggers model observers and cache busting.
            $this->deleteTopicWithRelations($childTopic);
        }

        if ($topic->poll) {
            // Remove poll options before deleting the poll itself to avoid orphan rows.
            $topic->poll->options()->delete();
            $topic->poll->delete();
        }

        foreach ($topic->attachments as $attachment) {
            // Remove stored files when topics are purged to keep disks lean and prevent ghosts.
            \Storage::disk('public')->delete($attachment->path);
            $attachment->delete();
        }

        if (method_exists($topic, 'comments')) {
            // Some topic flavours support comments via traits, so clean them when available.
            $topic->comments()->delete();
        }

        $topic->delete();
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
        $baseQuery = $this->group->topics()
            ->with(['childrenRecursive', 'parent']);

        $applyFilters = function ($builder): void {
            if ($this->search) {
                // Searching across titles and content ensures nested threads remain discoverable.
                $builder->where(function ($q): void {
                    $q->where('title', 'like', '%' . $this->search . '%')
                        ->orWhere('content', 'like', '%' . $this->search . '%');
                });
            }

            switch ($this->filter) {
                case 'mine':
                    $builder->where('user_id', auth()->id());
                    break;
                case 'pinned':
                    $builder->where('is_pinned', true);
                    break;
                case 'polls':
                    $builder->whereHas('poll');
                    break;
            }

            switch ($this->sortBy) {
                case 'latest':
                    $builder->latest();
                    break;
                case 'oldest':
                    $builder->oldest();
                    break;
                case 'most_commented':
                    $builder->withCount('comments')->orderBy('comments_count', 'desc');
                    break;
                case 'most_liked':
                    $builder->withCount('likes')->orderBy('likes_count', 'desc');
                    break;
            }
        };

        $pinnedQuery = clone $baseQuery;
        $applyFilters($pinnedQuery);

        $regularQuery = clone $baseQuery;
        $applyFilters($regularQuery);

        $pinnedTopics = $pinnedQuery->roots()->where('is_pinned', true)->get();
        $regularTopics = $regularQuery->roots()->where('is_pinned', false)->paginate(10);

        $availableParentTopics = $this->group->topics()
            ->roots()
            ->orderBy('title')
            ->get();

        return view('livewire.group.topics.index', [
            'pinnedTopics' => $pinnedTopics,
            'regularTopics' => $regularTopics,
            'availableParentTopics' => $availableParentTopics,
        ])->layout('layouts.app');
    }
}
