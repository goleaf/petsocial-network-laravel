<div class="space-y-8">
    {{-- Event management surface summarizing scheduling controls and RSVP actions. --}}

    @if ($canManageEvents)
        <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-900">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">
                {{ $editingEventId ? 'Update group event' : 'Create a new group event' }}
            </h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                Use this form to schedule in-person or online gatherings for your community.
            </p>

            <div class="mt-6 grid gap-4 md:grid-cols-2">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Title</label>
                    <input type="text" wire:model.defer="title" class="mt-1 w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100" />
                    @error('title')
                        <p class="mt-1 text-sm text-rose-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Start date</label>
                    <input type="datetime-local" wire:model.defer="startDate" class="mt-1 w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100" />
                    @error('startDate')
                        <p class="mt-1 text-sm text-rose-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">End date</label>
                    <input type="datetime-local" wire:model.defer="endDate" class="mt-1 w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100" />
                    @error('endDate')
                        <p class="mt-1 text-sm text-rose-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Location</label>
                    <input type="text" wire:model.defer="location" class="mt-1 w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100" />
                    @error('location')
                        <p class="mt-1 text-sm text-rose-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Location URL</label>
                    <input type="url" wire:model.defer="locationUrl" class="mt-1 w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100" />
                    @error('locationUrl')
                        <p class="mt-1 text-sm text-rose-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Online meeting URL</label>
                    <input type="url" wire:model.defer="onlineMeetingUrl" class="mt-1 w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100" />
                    @error('onlineMeetingUrl')
                        <p class="mt-1 text-sm text-rose-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Max attendees</label>
                    <input type="number" min="1" wire:model.defer="maxAttendees" class="mt-1 w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100" />
                    @error('maxAttendees')
                        <p class="mt-1 text-sm text-rose-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center space-x-3">
                    <label class="flex items-center space-x-2 text-sm font-medium text-slate-700 dark:text-slate-300">
                        <input type="checkbox" wire:model.defer="isOnline" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 dark:border-slate-600" />
                        <span>Online event</span>
                    </label>
                    <label class="flex items-center space-x-2 text-sm font-medium text-slate-700 dark:text-slate-300">
                        <input type="checkbox" wire:model.defer="isPublished" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 dark:border-slate-600" />
                        <span>Published</span>
                    </label>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Description</label>
                    <textarea wire:model.defer="description" rows="3" class="mt-1 w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"></textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-rose-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-6 flex items-center space-x-3">
                <button wire:click="saveEvent" class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">{{ $editingEventId ? 'Save changes' : 'Schedule event' }}</button>
                <button wire:click="resetEventForm" type="button" class="rounded-md border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-400 focus:ring-offset-2 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-800">Clear form</button>
            </div>
        </div>
    @endif

    <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-900">
        <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Upcoming events</h3>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">RSVP to stay informed and share the excitement with fellow members.</p>

        <div class="mt-4 space-y-4">
            @forelse ($upcomingEvents as $event)
                <div class="rounded-lg border border-slate-200 p-4 dark:border-slate-700">
                    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                        <div>
                            <h4 class="text-base font-semibold text-slate-900 dark:text-slate-100">{{ $event->title }}</h4>
                            <p class="text-sm text-slate-500 dark:text-slate-400">Hosted by {{ $event->creator->name }} on {{ $event->start_date->format('M j, Y g:i A') }}</p>
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                {{ $event->is_online ? 'Online' : $event->location }}
                                @if ($event->location_url)
                                    • <a href="{{ $event->location_url }}" class="text-emerald-600 hover:underline" target="_blank" rel="noopener">Directions</a>
                                @endif
                                @if ($event->online_meeting_url)
                                    • <a href="{{ $event->online_meeting_url }}" class="text-emerald-600 hover:underline" target="_blank" rel="noopener">Join link</a>
                                @endif
                            </p>
                            @if ($event->description)
                                <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">{{ $event->description }}</p>
                            @endif
                            <p class="mt-2 text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                Going: {{ $event->going_count }} • Interested: {{ $event->interested_count }} • Capacity: {{ $event->max_attendees ? $event->max_attendees : 'Unlimited' }}
                            </p>
                        </div>
                        <div class="flex flex-col gap-2">
                            <button wire:click="setRsvp({{ $event->id }}, 'going')" class="rounded-md bg-emerald-600 px-3 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Going</button>
                            <button wire:click="setRsvp({{ $event->id }}, 'interested')" class="rounded-md border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-800">Interested</button>
                            <button wire:click="setRsvp({{ $event->id }}, 'not_going')" class="rounded-md border border-rose-200 px-3 py-2 text-sm font-semibold text-rose-600 hover:bg-rose-50 dark:border-rose-600 dark:text-rose-300 dark:hover:bg-rose-950">Not going</button>
                            @if ($canManageEvents)
                                <button wire:click="editEvent({{ $event->id }})" class="rounded-md border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-800">Edit</button>
                                <button wire:click="deleteEvent({{ $event->id }})" class="rounded-md border border-rose-300 px-3 py-2 text-sm font-semibold text-rose-600 hover:bg-rose-50 dark:border-rose-600 dark:text-rose-300 dark:hover:bg-rose-950">Delete</button>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-sm text-slate-500 dark:text-slate-400">There are no upcoming events scheduled. Check back soon!</p>
            @endforelse
        </div>
    </div>

    <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-900">
        <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Recent events</h3>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Catch up on the gatherings you may have missed.</p>

        <div class="mt-4 space-y-4">
            @forelse ($pastEvents as $event)
                <div class="rounded-lg border border-slate-200 p-4 dark:border-slate-700">
                    <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                        <div>
                            <h4 class="text-base font-semibold text-slate-900 dark:text-slate-100">{{ $event->title }}</h4>
                            <p class="text-sm text-slate-500 dark:text-slate-400">Hosted {{ $event->start_date->diffForHumans() }} by {{ $event->creator->name }}</p>
                        </div>
                        <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ $event->going_count }} went • {{ $event->interested_count }} interested</p>
                    </div>
                </div>
            @empty
                <p class="text-sm text-slate-500 dark:text-slate-400">No recent events yet. Hosting gatherings will populate this history.</p>
            @endforelse
        </div>
    </div>
</div>
