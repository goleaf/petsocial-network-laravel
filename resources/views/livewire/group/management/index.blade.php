{{-- Group management index surfaces community discovery controls and recommendations. --}}
<div data-testid="group-management-index-root" class="space-y-10">
    {{-- Flash notifications keep members informed about join and leave actions. --}}
    @if (session()->has('message'))
        <div class="rounded-lg border border-pink-200 bg-pink-50 px-4 py-3 text-sm text-pink-800 shadow-sm dark:border-pink-900/60 dark:bg-pink-950/40 dark:text-pink-200">
            {{ session('message') }}
        </div>
    @endif

    {{-- Heading anchors the discovery experience with context and access to creation workflows. --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="space-y-1">
            <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-50">{{ __('Group Discovery') }}</h1>
            <p class="text-sm text-slate-600 dark:text-slate-400">
                {{ __('Find thriving communities aligned with your interests and the friends you trust.') }}
            </p>
        </div>

        <button
            type="button"
            wire:click="$set('showCreateModal', true)"
            class="inline-flex items-center justify-center gap-2 rounded-full bg-pink-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-pink-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-pink-500 dark:bg-pink-500 dark:hover:bg-pink-400"
        >
            {{-- Expose an affordance for quickly creating a new group. --}}
            <span aria-hidden="true" class="text-base">＋</span>
            <span>{{ __('Create Group') }}</span>
        </button>
    </div>

    {{-- Search and filter controls allow members to shape the discovery results. --}}
    <div class="flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white/80 p-6 shadow-sm backdrop-blur dark:border-slate-700 dark:bg-slate-900/70">
        <label class="flex items-center gap-3 rounded-xl border border-transparent bg-slate-100 px-4 py-2 ring-1 ring-transparent focus-within:border-pink-400 focus-within:ring-pink-200 dark:bg-slate-800 dark:focus-within:border-pink-500 dark:focus-within:ring-pink-800">
            {{-- Search input ties into Livewire to filter groups in real time. --}}
            <span class="text-sm font-medium text-slate-600 dark:text-slate-300">{{ __('Search groups') }}</span>
            <input
                type="search"
                wire:model.debounce.400ms="search"
                placeholder="{{ __('Search by name or description') }}"
                class="flex-1 border-0 bg-transparent text-sm text-slate-900 placeholder-slate-400 focus:outline-none dark:text-slate-50"
            />
        </label>

        {{-- Filter chips expose quick pivots between membership and visibility states. --}}
        <div class="flex flex-wrap items-center gap-2">
            @php
                $filters = [
                    'all' => __('All groups'),
                    'my' => __('My groups'),
                    'open' => __('Open'),
                    'closed' => __('Closed'),
                    'secret' => __('Secret'),
                ];
            @endphp

            @foreach ($filters as $value => $label)
                <button
                    type="button"
                    wire:click="$set('filter', '{{ $value }}')"
                    @class([
                        'rounded-full border px-4 py-2 text-sm font-medium transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2',
                        'border-pink-400 bg-pink-50 text-pink-700 dark:border-pink-500 dark:bg-pink-900/40 dark:text-pink-200' => $filter === $value,
                        'border-slate-200 text-slate-600 hover:border-pink-300 hover:text-pink-600 dark:border-slate-700 dark:text-slate-300 dark:hover:border-pink-500 dark:hover:text-pink-200' => $filter !== $value,
                    ])
                >
                    {{ $label }}
                </button>
            @endforeach
        </div>
    </div>

    {{-- Recommendations tailored to the viewer's interests. --}}
    <section class="space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ __('Recommended for your interests') }}</h2>
            <span class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Personalised') }}</span>
        </div>

        @if ($interestRecommendations->isEmpty())
            <p class="rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-sm text-slate-600 dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-400">
                {{ __('Join a few communities to unlock tailored suggestions based on the topics you love.') }}
            </p>
        @else
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($interestRecommendations as $group)
                    @php
                        // Precompute membership states so button labels stay accurate.
                        $isMember = $viewer ? $group->isMember($viewer) : false;
                        $isPending = $viewer ? $group->isPendingMember($viewer) : false;
                    @endphp

                    <article
                        wire:key="interest-group-{{ $group->id }}"
                        class="flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white/80 p-5 shadow-sm transition hover:border-pink-300 hover:shadow-md dark:border-slate-700 dark:bg-slate-900/70 dark:hover:border-pink-500"
                    >
                        {{-- Group identity block shows the name, category, and optional location. --}}
                        <div class="space-y-1">
                            <h3 class="text-base font-semibold text-slate-900 dark:text-slate-50">{{ $group->name }}</h3>
                            <div class="flex flex-wrap items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                                @if ($group->category)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-pink-100 px-3 py-1 font-medium text-pink-700 dark:bg-pink-900/50 dark:text-pink-200">
                                        {{ $group->category->name }}
                                    </span>
                                @endif
                                @if ($group->location)
                                    <span class="inline-flex items-center gap-1">
                                        <svg aria-hidden="true" class="h-3.5 w-3.5 text-slate-400" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 2a6 6 0 00-6 6c0 4.418 6 10 6 10s6-5.582 6-10a6 6 0 00-6-6zm0 8a2 2 0 110-4 2 2 0 010 4z" clip-rule="evenodd" />
                                        </svg>
                                        <span>{{ $group->location }}</span>
                                    </span>
                                @endif
                                <span class="inline-flex items-center gap-1">
                                    <svg aria-hidden="true" class="h-3.5 w-3.5 text-slate-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M13 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path fill-rule="evenodd" d="M5.5 15a4.5 4.5 0 019 0v1.25a.75.75 0 01-.75.75h-7.5a.75.75 0 01-.75-.75V15z" clip-rule="evenodd" />
                                    </svg>
                                    <span>{{ trans_choice('{0}No members yet|{1}1 member|[2,*]:count members', $group->members_count, ['count' => $group->members_count]) }}</span>
                                </span>
                            </div>
                        </div>

                        <p class="text-sm text-slate-600 dark:text-slate-400">
                            {{ $group->description ? \Illuminate\Support\Str::limit($group->description, 110) : __('This group is getting ready to share its story.') }}
                        </p>

                        <div class="mt-auto flex items-center justify-between gap-3">
                            <span class="text-xs font-medium uppercase tracking-wide text-pink-600 dark:text-pink-300">{{ __('Trending in your circles') }}</span>

                            @if ($isMember)
                                <button
                                    type="button"
                                    wire:click="leaveGroup({{ $group->id }})"
                                    class="rounded-full border border-slate-300 px-4 py-2 text-sm font-medium text-slate-600 transition hover:border-slate-400 hover:text-slate-800 dark:border-slate-600 dark:text-slate-300 dark:hover:border-slate-500 dark:hover:text-slate-100"
                                >
                                    {{ __('Leave group') }}
                                </button>
                            @elseif ($isPending)
                                <span class="rounded-full border border-dashed border-slate-300 px-4 py-2 text-sm font-medium text-slate-500 dark:border-slate-600 dark:text-slate-400">
                                    {{ __('Request pending') }}
                                </span>
                            @else
                                <button
                                    type="button"
                                    wire:click="joinGroup({{ $group->id }})"
                                    class="rounded-full bg-pink-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-pink-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-pink-500 dark:bg-pink-500 dark:hover:bg-pink-400"
                                >
                                    {{ __('Join group') }}
                                </button>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>

    {{-- Recommendations powered by the member's friend network. --}}
    <section class="space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ __('Popular with your friends') }}</h2>
            <span class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Social signal') }}</span>
        </div>

        @if ($connectionRecommendations->isEmpty())
            <p class="rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-sm text-slate-600 dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-400">
                {{ __('Add a few friends to see where your community is already gathering.') }}
            </p>
        @else
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($connectionRecommendations as $group)
                    @php
                        $isMember = $viewer ? $group->isMember($viewer) : false;
                        $isPending = $viewer ? $group->isPendingMember($viewer) : false;
                        $friendCount = $group->friend_members_count ?? 0;
                    @endphp

                    <article
                        wire:key="connection-group-{{ $group->id }}"
                        class="flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white/80 p-5 shadow-sm transition hover:border-pink-300 hover:shadow-md dark:border-slate-700 dark:bg-slate-900/70 dark:hover:border-pink-500"
                    >
                        <div class="space-y-1">
                            <h3 class="text-base font-semibold text-slate-900 dark:text-slate-50">{{ $group->name }}</h3>
                            <div class="flex flex-wrap items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                                @if ($friendCount > 0)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-3 py-1 font-medium text-slate-600 dark:bg-slate-800 dark:text-slate-200">
                                        <svg aria-hidden="true" class="h-3.5 w-3.5 text-pink-500" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M10 3a3 3 0 00-3 3v1H5a2 2 0 00-2 2v4.5A1.5 1.5 0 004.5 15h11a1.5 1.5 0 001.5-1.5V9a2 2 0 00-2-2h-2V6a3 3 0 00-3-3z" />
                                        </svg>
                                        {{ $friendCount }} {{ \Illuminate\Support\Str::plural(__('friend'), $friendCount) }} {{ __('inside') }}
                                    </span>
                                @endif
                                <span class="inline-flex items-center gap-1">
                                    <svg aria-hidden="true" class="h-3.5 w-3.5 text-slate-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M4 6a2 2 0 012-2h8a2 2 0 012 2v8a2 2 0 01-2 2H6a2 2 0 01-2-2V6z" />
                                    </svg>
                                    <span class="capitalize">{{ $group->visibility }}</span>
                                </span>
                                <span class="inline-flex items-center gap-1">
                                    <svg aria-hidden="true" class="h-3.5 w-3.5 text-slate-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M13 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path fill-rule="evenodd" d="M5.5 15a4.5 4.5 0 019 0v1.25a.75.75 0 01-.75.75h-7.5a.75.75 0 01-.75-.75V15z" clip-rule="evenodd" />
                                    </svg>
                                    <span>{{ trans_choice('{0}No members yet|{1}1 member|[2,*]:count members', $group->members_count, ['count' => $group->members_count]) }}</span>
                                </span>
                            </div>
                        </div>

                        <p class="text-sm text-slate-600 dark:text-slate-400">
                            {{ $group->description ? \Illuminate\Support\Str::limit($group->description, 110) : __('Your friends are building this space together right now.') }}
                        </p>

                        <div class="mt-auto flex items-center justify-end gap-3">
                            @if ($isMember)
                                <button
                                    type="button"
                                    wire:click="leaveGroup({{ $group->id }})"
                                    class="rounded-full border border-slate-300 px-4 py-2 text-sm font-medium text-slate-600 transition hover:border-slate-400 hover:text-slate-800 dark:border-slate-600 dark:text-slate-300 dark:hover:border-slate-500 dark:hover:text-slate-100"
                                >
                                    {{ __('Leave group') }}
                                </button>
                            @elseif ($isPending)
                                <span class="rounded-full border border-dashed border-slate-300 px-4 py-2 text-sm font-medium text-slate-500 dark:border-slate-600 dark:text-slate-400">
                                    {{ __('Request pending') }}
                                </span>
                            @else
                                <button
                                    type="button"
                                    wire:click="joinGroup({{ $group->id }})"
                                    class="rounded-full bg-pink-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-pink-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-pink-500 dark:bg-pink-500 dark:hover:bg-pink-400"
                                >
                                    {{ __('Join group') }}
                                </button>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>

    {{-- Primary directory that respects search, filters, and pagination. --}}
    <section class="space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ __('All groups') }}</h2>
            <span class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Directory') }}</span>
        </div>

        @if ($groups->isEmpty())
            <p class="rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-sm text-slate-600 dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-400">
                {{ __('No groups matched your current filters. Try broadening your search or clear the filter chips above.') }}
            </p>
        @else
            <div class="grid gap-4 lg:grid-cols-2">
                @foreach ($groups as $group)
                    @php
                        $isMember = $viewer ? $group->isMember($viewer) : false;
                        $isPending = $viewer ? $group->isPendingMember($viewer) : false;
                    @endphp

                    <article
                        wire:key="directory-group-{{ $group->id }}"
                        class="flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white/80 p-5 shadow-sm transition hover:border-pink-300 hover:shadow-md dark:border-slate-700 dark:bg-slate-900/70 dark:hover:border-pink-500"
                    >
                        <div class="flex flex-col gap-2">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <h3 class="text-base font-semibold text-slate-900 dark:text-slate-50">{{ $group->name }}</h3>
                                    <p class="text-sm text-slate-600 dark:text-slate-400">
                                        {{ $group->description ? \Illuminate\Support\Str::limit($group->description, 140) : __('This group has not published a description yet.') }}
                                    </p>
                                </div>
                                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-slate-600 dark:bg-slate-800 dark:text-slate-200">
                                    {{ $group->visibility }}
                                </span>
                            </div>

                            <div class="flex flex-wrap items-center gap-3 text-xs text-slate-500 dark:text-slate-400">
                                @if ($group->category)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-pink-100 px-3 py-1 font-medium text-pink-700 dark:bg-pink-900/50 dark:text-pink-200">
                                        {{ $group->category->name }}
                                    </span>
                                @endif
                                @if ($group->location)
                                    <span class="inline-flex items-center gap-1">
                                        <svg aria-hidden="true" class="h-3.5 w-3.5 text-slate-400" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 2a6 6 0 00-6 6c0 4.418 6 10 6 10s6-5.582 6-10a6 6 0 00-6-6zm0 8a2 2 0 110-4 2 2 0 010 4z" clip-rule="evenodd" />
                                        </svg>
                                        <span>{{ $group->location }}</span>
                                    </span>
                                @endif
                                <span class="inline-flex items-center gap-1">
                                    <svg aria-hidden="true" class="h-3.5 w-3.5 text-slate-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M13 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path fill-rule="evenodd" d="M5.5 15a4.5 4.5 0 019 0v1.25a.75.75 0 01-.75.75h-7.5a.75.75 0 01-.75-.75V15z" clip-rule="evenodd" />
                                    </svg>
                                    <span>{{ trans_choice('{0}No members yet|{1}1 member|[2,*]:count members', $group->members_count, ['count' => $group->members_count]) }}</span>
                                </span>
                            </div>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Manage membership') }}</span>

                            @if ($isMember)
                                <button
                                    type="button"
                                    wire:click="leaveGroup({{ $group->id }})"
                                    class="rounded-full border border-slate-300 px-4 py-2 text-sm font-medium text-slate-600 transition hover:border-slate-400 hover:text-slate-800 dark:border-slate-600 dark:text-slate-300 dark:hover:border-slate-500 dark:hover:text-slate-100"
                                >
                                    {{ __('Leave group') }}
                                </button>
                            @elseif ($isPending)
                                <span class="rounded-full border border-dashed border-slate-300 px-4 py-2 text-sm font-medium text-slate-500 dark:border-slate-600 dark:text-slate-400">
                                    {{ __('Request pending') }}
                                </span>
                            @else
                                <button
                                    type="button"
                                    wire:click="joinGroup({{ $group->id }})"
                                    class="rounded-full bg-pink-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-pink-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-pink-500 dark:bg-pink-500 dark:hover:bg-pink-400"
                                >
                                    {{ __('Join group') }}
                                </button>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="pt-4">
                {{-- Pagination keeps navigation accessible for long group directories. --}}
                {{ $groups->links() }}
            </div>
        @endif
    </section>
</div>
