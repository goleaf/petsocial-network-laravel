<?php

namespace App\Http\Livewire\Group\Events;

use App\Models\Group\Group;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

class Index extends Component
{
    /**
     * The group being managed within the component.
     */
    public Group $group;

    /**
     * Toggle for the event modal visibility state.
     */
    public bool $showEventModal = false;

    /**
     * Stores the identifier for an event currently being edited.
     */
    public ?int $editingEventId = null;

    /**
     * Form fields backing the event creation and editing workflow.
     */
    public string $title = '';
    public ?string $description = null;
    public ?string $startDate = null;
    public ?string $endDate = null;
    public ?string $location = null;
    public ?string $locationUrl = null;
    public bool $isOnline = false;
    public ?string $onlineMeetingUrl = null;
    public ?int $maxAttendees = null;
    public bool $isPublished = true;

    /**
     * Validation rules ensure consistent scheduling details.
     */
    protected array $rules = [
        'title' => 'required|string|max:150',
        'description' => 'nullable|string|max:2000',
        'startDate' => 'required|date',
        'endDate' => 'nullable|date|after_or_equal:startDate',
        'location' => 'nullable|string|max:255',
        'locationUrl' => 'nullable|url|max:255',
        'isOnline' => 'boolean',
        'onlineMeetingUrl' => 'nullable|url|max:255',
        'maxAttendees' => 'nullable|integer|min:1|max:5000',
        'isPublished' => 'boolean',
    ];

    /**
     * Ensure the authenticated viewer can access the supplied group.
     */
    public function mount(Group $group): void
    {
        $this->group = $group;

        if (!$this->group->isVisibleTo(Auth::user())) {
            abort(403, 'You do not have permission to view these group events.');
        }
    }

    /**
     * Authorisation helper that restricts event management to admins or moderators.
     */
    protected function ensureCanManageEvents(): void
    {
        $user = Auth::user();

        if (!$user || !($this->group->isAdmin($user) || $this->group->isModerator($user))) {
            throw new AuthorizationException('You are not allowed to manage events for this group.');
        }
    }

    /**
     * Populate the form with the chosen event data so it can be updated.
     */
    public function editEvent(int $eventId): void
    {
        $this->ensureCanManageEvents();

        $event = $this->group->events()->findOrFail($eventId);

        $this->editingEventId = $event->id;
        $this->title = $event->title;
        $this->description = $event->description;
        $this->startDate = $event->start_date?->format('Y-m-d\TH:i');
        $this->endDate = $event->end_date?->format('Y-m-d\TH:i');
        $this->location = $event->location;
        $this->locationUrl = $event->location_url;
        $this->isOnline = (bool) $event->is_online;
        $this->onlineMeetingUrl = $event->online_meeting_url;
        $this->maxAttendees = $event->max_attendees;
        $this->isPublished = (bool) $event->is_published;
        $this->showEventModal = true;
    }

    /**
     * Reset the modal form state so new events start with a clean slate.
     */
    public function resetEventForm(): void
    {
        $this->editingEventId = null;
        $this->title = '';
        $this->description = null;
        $this->startDate = null;
        $this->endDate = null;
        $this->location = null;
        $this->locationUrl = null;
        $this->isOnline = false;
        $this->onlineMeetingUrl = null;
        $this->maxAttendees = null;
        $this->isPublished = true;
    }

    /**
     * Persist a new event or update the event currently being edited.
     */
    public function saveEvent(): void
    {
        $this->ensureCanManageEvents();

        $data = $this->validate();
        $data['start_date'] = Carbon::parse($data['startDate']);
        $data['end_date'] = isset($data['endDate']) ? Carbon::parse($data['endDate']) : null;
        $data['is_online'] = (bool) $data['isOnline'];
        $data['is_published'] = (bool) $data['isPublished'];
        $data['location_url'] = $data['locationUrl'];
        $data['online_meeting_url'] = $data['onlineMeetingUrl'];
        $data['max_attendees'] = $data['maxAttendees'] ?: null;

        unset(
            $data['startDate'],
            $data['endDate'],
            $data['isOnline'],
            $data['isPublished'],
            $data['locationUrl'],
            $data['onlineMeetingUrl'],
            $data['maxAttendees']
        );

        if ($this->editingEventId) {
            $event = $this->group->events()->findOrFail($this->editingEventId);
            $event->update($data);
        } else {
            $event = $this->group->events()->create($data + [
                'user_id' => Auth::id(),
            ]);
        }

        $event->clearCache();
        $this->group->clearCache();

        $this->resetEventForm();
        $this->showEventModal = false;

        Session::flash('message', 'Event saved successfully.');
    }

    /**
     * Remove an event from the schedule when moderation requires it.
     */
    public function deleteEvent(int $eventId): void
    {
        $this->ensureCanManageEvents();

        $event = $this->group->events()->findOrFail($eventId);
        $event->delete();
        $event->clearCache();
        $this->group->clearCache();

        Session::flash('message', 'Event deleted successfully.');
    }

    /**
     * Let the current member declare an RSVP stance for the event.
     */
    public function setRsvp(int $eventId, string $status): void
    {
        $allowedStatuses = ['going', 'interested', 'not_going'];

        if (!in_array($status, $allowedStatuses, true)) {
            Session::flash('message', 'The supplied RSVP selection is invalid.');

            return;
        }

        if (!Auth::check()) {
            $this->redirectRoute('login');

            return;
        }

        $user = Auth::user();

        if (!$this->group->isMember($user)) {
            Session::flash('message', 'You must be an active group member before responding to an event.');

            return;
        }

        $event = $this->group->events()->with('attendees')->findOrFail($eventId);

        if ($status === 'going') {
            $existingGoing = $event->attendees
                ->where('pivot.status', 'going')
                ->where('id', $user->id)
                ->first();

            if (!$existingGoing && $event->isAtCapacity()) {
                Session::flash('message', 'This event has reached capacity, so you cannot mark yourself as going.');

                return;
            }
        }

        $event->attendees()->syncWithoutDetaching([
            $user->id => [
                'status' => $status,
                'reminder_set' => false,
            ],
        ]);

        $event->clearCache();
        $this->group->clearCache();

        Session::flash('message', 'Your RSVP has been recorded.');
    }

    /**
     * Provide the rendered response with grouped upcoming and past events.
     */
    public function render()
    {
        $now = Carbon::now();

        $upcomingEvents = $this->group->events()
            ->with(['creator:id,name', 'attendees' => fn ($query) => $query->select('users.id', 'name')])
            ->where('start_date', '>=', $now)
            ->orderBy('start_date')
            ->get();

        $pastEvents = $this->group->events()
            ->with(['creator:id,name', 'attendees' => fn ($query) => $query->select('users.id', 'name')])
            ->where('start_date', '<', $now)
            ->orderByDesc('start_date')
            ->limit(10)
            ->get();

        $canManageEvents = false;
        $user = Auth::user();

        if ($user) {
            $canManageEvents = $this->group->isAdmin($user) || $this->group->isModerator($user);
        }

        return view('livewire.group.events.index', [
            'upcomingEvents' => $upcomingEvents,
            'pastEvents' => $pastEvents,
            'canManageEvents' => $canManageEvents,
        ])->layout('layouts.app');
    }
}
