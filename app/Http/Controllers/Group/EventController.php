<?php

namespace App\Http\Controllers\Group;

use App\Http\Controllers\Controller;
use App\Models\Group\Event;
use App\Models\Group\Group;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class EventController extends Controller
{
    /**
     * Display the event detail page so members can review logistics and export calendar files.
     */
    public function show(Request $request, Group $group, Event $event)
    {
        $this->ensureEventContext($group, $event);
        $this->guardGroupVisibility($request->user(), $group, $event);

        // Share the event information with the Blade layer for rendering.
        return view('group.events.show', [
            'group' => $group,
            'event' => $event,
        ]);
    }

    /**
     * Stream an iCalendar export so members can sync the event with third-party calendars.
     */
    public function export(Request $request, Group $group, Event $event): Response
    {
        $this->ensureEventContext($group, $event);
        $this->guardGroupVisibility($request->user(), $group, $event);

        $payload = $event->generateICalendar();
        $fileName = sprintf('group-event-%s.ics', Str::slug($event->title) ?: $event->id);

        // Return the ICS payload with attachment headers so browsers trigger a download.
        return response($payload, 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

    /**
     * Ensure the event belongs to the provided group so crafted URLs cannot leak other events.
     */
    protected function ensureEventContext(Group $group, Event $event): void
    {
        if ($event->group_id !== $group->id) {
            abort(404);
        }
    }

    /**
     * Confirm that the authenticated user can view the group and event combination.
     */
    protected function guardGroupVisibility(?User $user, Group $group, Event $event): void
    {
        if (!$group->isVisibleTo($user)) {
            abort(403, 'Group visibility restricted.');
        }

        if (!$event->is_published && !$user?->isAdmin() && (!$user || !$group->isMember($user))) {
            abort(403, 'Event is not published.');
        }
    }
}
