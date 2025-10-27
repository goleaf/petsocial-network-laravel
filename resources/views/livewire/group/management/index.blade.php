{{--
    The management dashboard surfaces category filters and group listings so community builders
    can find and curate spaces that match their interests. The markup intentionally mirrors the
    Tailwind-first design language captured in docs/ux-style-guide.md.
--}}
<div class="space-y-10" data-testid="group-management-index-root">
    {{-- Category explorer allows quick navigation between topical communities. --}}
    <section class="space-y-4">
        <header class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-semibold text-slate-900 dark:text-slate-50">{{ __('Group Categories') }}</h2>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">
                    {{ __('Browse communities organised by shared interests and purposes.') }}
                </p>
            </div>
            <button
                type="button"
                wire:click="setCategoryFilter"
                class="inline-flex items-center rounded-full border border-slate-300 px-3 py-1 text-sm font-medium text-slate-700 transition hover:border-slate-400 hover:text-slate-900 dark:border-slate-600 dark:text-slate-200 dark:hover:border-slate-500 dark:hover:text-slate-50"
            >
                {{ __('View All') }}
            </button>
        </header>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @foreach ($categories as $category)
                @php
                    $isActive = (string) $categoryFilter === (string) $category['id'];
                @endphp

                <button
                    type="button"
                    wire:click="setCategoryFilter({{ $category['id'] }})"
                    class="flex h-full flex-col items-start rounded-2xl border p-4 text-left transition focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 {{ $isActive ? 'border-paw-500 bg-paw-50/60 text-paw-800 dark:border-paw-400 dark:bg-paw-500/20 dark:text-paw-100 focus-visible:ring-paw-400' : 'border-slate-200 bg-white text-slate-800 hover:border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:hover:border-slate-600 focus-visible:ring-paw-300' }}"
                >
                    {{-- Icon placeholder keeps layout flexible even when icons are not yet supplied. --}}
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-paw-500/10 text-paw-600 dark:bg-paw-400/10 dark:text-paw-200">
                        {{ $category['icon'] ? ucfirst(substr($category['icon'], 3, 1)) : '◎' }}
                    </span>
                    <span class="mt-3 text-lg font-semibold">{{ $category['name'] }}</span>
                    @if ($category['description'])
                        <span class="mt-2 text-sm text-slate-600 dark:text-slate-300">
                            {{ $category['description'] }}
                        </span>
                    @endif
                    <span class="mt-4 inline-flex items-center gap-1 text-sm font-medium text-slate-700 dark:text-slate-200">
                        <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                        {{ trans_choice('{1} :count group|[2,*] :count groups', $category['group_count'], ['count' => $category['group_count']]) }}
                    </span>
                </button>
            @endforeach
        </div>
    </section>

    {{-- Group directory summarises each community so members can evaluate at a glance. --}}
    <section class="space-y-6">
        <header class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
            <div class="flex flex-col gap-2 md:flex-row md:items-center md:gap-4">
                <label class="flex flex-col text-sm font-medium text-slate-700 dark:text-slate-200">
                    <span>{{ __('Search Groups') }}</span>
                    <input
                        type="search"
                        wire:model.debounce.500ms="search"
                        placeholder="{{ __('Find a community...') }}"
                        class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm transition focus:border-paw-500 focus:outline-none focus:ring-2 focus:ring-paw-500/40 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"
                    />
                </label>

                <label class="flex flex-col text-sm font-medium text-slate-700 dark:text-slate-200">
                    <span>{{ __('Visibility') }}</span>
                    <select
                        wire:model="filter"
                        class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm transition focus:border-paw-500 focus:outline-none focus:ring-2 focus:ring-paw-500/40 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"
                    >
                        <option value="all">{{ __('All groups') }}</option>
                        <option value="my">{{ __('My memberships') }}</option>
                        <option value="open">{{ __('Open') }}</option>
                        <option value="closed">{{ __('Closed') }}</option>
                        <option value="secret">{{ __('Secret') }}</option>
                    </select>
                </label>

                <div class="text-sm font-medium text-slate-700 dark:text-slate-200">
                    <span class="block">{{ __('Category Filter') }}</span>
                    <span class="mt-1 inline-flex items-center gap-2 rounded-full border border-slate-300 px-3 py-1 text-xs text-slate-700 dark:border-slate-600 dark:text-slate-100">
                        <span class="font-semibold">{{ $categoryFilter === 'all' ? __('All categories') : collect($categories)->firstWhere('id', (int) $categoryFilter)['name'] ?? __('Unknown') }}</span>
                        <button
                            type="button"
                            wire:click="setCategoryFilter"
                            class="text-paw-600 transition hover:text-paw-700 dark:text-paw-300 dark:hover:text-paw-200"
                        >
                            {{ __('Reset') }}
                        </button>
                    </span>
                </div>
            </div>

            <button
                type="button"
                wire:click="$set('showCreateModal', true)"
                class="inline-flex items-center justify-center rounded-lg bg-paw-600 px-4 py-2 text-sm font-semibold text-white shadow transition hover:bg-paw-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-paw-400"
            >
                {{ __('Create Group') }}
            </button>
        </header>

        <div class="grid gap-4 lg:grid-cols-2">
            @forelse ($groups as $group)
                <article class="flex h-full flex-col justify-between rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:shadow-md dark:border-slate-700 dark:bg-slate-900">
                    <div class="space-y-2">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h3 class="text-xl font-semibold text-slate-900 dark:text-slate-50">{{ $group->name }}</h3>
                                <p class="text-sm text-slate-600 dark:text-slate-300">{{ $group->description }}</p>
                            </div>
                            <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-slate-700 dark:bg-slate-800 dark:text-slate-200">
                                {{ ucfirst($group->visibility) }}
                            </span>
                        </div>

                        @if ($group->category)
                            <p class="text-xs font-medium text-paw-600 dark:text-paw-300">
                                {{ __('Category: :name', ['name' => $group->category->name]) }}
                            </p>
                        @endif
                    </div>

                    <div class="mt-4 flex items-center justify-between text-sm text-slate-600 dark:text-slate-300">
                        <span>{{ trans_choice('{1} :count member|[2,*] :count members', $group->members_count, ['count' => $group->members_count]) }}</span>
                        <a
                            href="{{ route('group.detail', $group) }}"
                            class="inline-flex items-center gap-1 text-paw-600 transition hover:text-paw-700 dark:text-paw-300 dark:hover:text-paw-200"
                        >
                            {{ __('View Group') }}
                            <span aria-hidden="true">&rarr;</span>
                        </a>
                    </div>
                </article>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-300 p-10 text-center text-slate-600 dark:border-slate-600 dark:text-slate-300">
                    {{ __('No groups match your filters just yet. Try adjusting the search or category.') }}
                </div>
            @endforelse
        </div>

        <div>
            {{-- Livewire pagination keeps parity with the Tailwind renderer shipped by Laravel Breeze. --}}
            {{ $groups->links() }}
        </div>
    </section>
</div>
