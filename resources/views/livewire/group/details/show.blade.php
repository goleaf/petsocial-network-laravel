<div class="space-y-6" id="group-detail-surface">
    {{-- Flash messaging keeps members informed about actions like sharing or deleting resources. --}}
    @if (session()->has('message'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800 dark:border-emerald-800/60 dark:bg-emerald-950/40 dark:text-emerald-200">
            {{ session('message') }}
        </div>
    @endif

    <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-900">
        {{-- Group overview summarises high-level details for quick scanning. --}}
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900 dark:text-white">{{ $group->name }}</h1>
                <p class="mt-2 max-w-3xl text-sm text-slate-600 dark:text-slate-300">{{ $group->description }}</p>
            </div>
            <dl class="grid grid-cols-3 gap-4 text-center text-sm text-slate-700 dark:text-slate-200">
                <div class="rounded-lg border border-slate-200 p-3 dark:border-slate-700">
                    <dt class="font-medium">Members</dt>
                    <dd class="mt-1 text-lg font-semibold">{{ $group->members_count }}</dd>
                </div>
                <div class="rounded-lg border border-slate-200 p-3 dark:border-slate-700">
                    <dt class="font-medium">Topics</dt>
                    <dd class="mt-1 text-lg font-semibold">{{ $group->topics_count }}</dd>
                </div>
                <div class="rounded-lg border border-slate-200 p-3 dark:border-slate-700">
                    <dt class="font-medium">Resources</dt>
                    <dd class="mt-1 text-lg font-semibold">{{ $group->resources_count }}</dd>
                </div>
            </dl>
        </div>
    </section>

    <nav class="flex flex-wrap gap-2 text-sm font-medium text-slate-600 dark:text-slate-300" aria-label="Group sections">
        {{-- Tab buttons reuse Livewire state to flip between detail panels. --}}
        @foreach ([
            'topics' => 'Topics',
            'members' => 'Members',
            'resources' => 'Resources',
        ] as $tabKey => $label)
            <button
                type="button"
                wire:click="setActiveTab('{{ $tabKey }}')"
                @class([
                    'rounded-full px-4 py-2 transition focus:outline-none focus:ring-2 focus:ring-pink-500 focus:ring-offset-2 dark:focus:ring-offset-slate-900',
                    'bg-pink-600 text-white shadow-sm' => $activeTab === $tabKey,
                    'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-200' => $activeTab !== $tabKey,
                ])
            >
                {{ $label }}
            </button>
        @endforeach
    </nav>

    @if ($activeTab === 'resources')
        <section class="space-y-6">
            {{-- Resource submission form toggles between link and document workflows. --}}
            <form wire:submit.prevent="shareResource" class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Share a resource</h2>

                <div class="mt-4 grid gap-5 md:grid-cols-2">
                    <div>
                        <label for="resourceTitle" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Title</label>
                        <input
                            id="resourceTitle"
                            type="text"
                            wire:model.defer="resourceTitle"
                            class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-pink-500 focus:outline-none focus:ring-2 focus:ring-pink-500 dark:border-slate-600 dark:bg-slate-800 dark:text-white"
                            placeholder="Weekly planning document"
                        />
                        @error('resourceTitle')
                            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Resource type</label>
                        <div class="mt-2 flex gap-3">
                            <label class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-200">
                                <input type="radio" value="link" wire:model="resourceType" class="h-4 w-4 text-pink-600 focus:ring-pink-500" />
                                Link
                            </label>
                            <label class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-200">
                                <input type="radio" value="document" wire:model="resourceType" class="h-4 w-4 text-pink-600 focus:ring-pink-500" />
                                Document
                            </label>
                        </div>
                        @error('resourceType')
                            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-4">
                    <label for="resourceDescription" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Description</label>
                    <textarea
                        id="resourceDescription"
                        wire:model.defer="resourceDescription"
                        rows="3"
                        class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-pink-500 focus:outline-none focus:ring-2 focus:ring-pink-500 dark:border-slate-600 dark:bg-slate-800 dark:text-white"
                        placeholder="Highlight how this resource helps the community"
                    ></textarea>
                    @error('resourceDescription')
                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                    @enderror
                </div>

                @if ($resourceType === 'link')
                    <div class="mt-4">
                        <label for="resourceUrl" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Link URL</label>
                        <input
                            id="resourceUrl"
                            type="url"
                            wire:model.defer="resourceUrl"
                            class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-pink-500 focus:outline-none focus:ring-2 focus:ring-pink-500 dark:border-slate-600 dark:bg-slate-800 dark:text-white"
                            placeholder="https://example.com/resource"
                        />
                        @error('resourceUrl')
                            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>
                @else
                    <div class="mt-4">
                        <label for="resourceDocument" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Upload document</label>
                        <input
                            id="resourceDocument"
                            type="file"
                            wire:model="resourceDocument"
                            class="mt-1 block w-full text-sm text-slate-600 file:mr-4 file:rounded-md file:border-0 file:bg-pink-600 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-pink-500 dark:text-slate-300"
                        />
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Accepted formats: PDF, DOC, PPT, XLS up to 5MB.</p>
                        @error('resourceDocument')
                            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>
                @endif

                <div class="mt-6 flex items-center justify-end gap-3">
                    <button type="button" wire:click="resetResourceForm" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 transition hover:border-slate-300 hover:text-slate-700 dark:border-slate-600 dark:text-slate-300 dark:hover:border-slate-500">Reset</button>
                    <button type="submit" class="rounded-lg bg-pink-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-pink-500 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:ring-offset-2 dark:focus:ring-offset-slate-900">Share resource</button>
                </div>
            </form>

            <div class="space-y-4">
                {{-- Resource list surfaces the curated knowledge base for the community. --}}
                @forelse ($resources as $resource)
                    <article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900" wire:key="resource-{{ $resource->id }}">
                        <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                            <div>
                                <h3 class="text-base font-semibold text-slate-900 dark:text-white">{{ $resource->title }}</h3>
                                <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ $resource->description }}</p>

                                @if ($resource->isLink())
                                    <a href="{{ $resource->url }}" target="_blank" rel="noopener" class="mt-2 inline-flex items-center gap-2 text-sm font-medium text-pink-600 hover:text-pink-500">
                                        Visit link
                                        <x-icons.arrow-right-long class="h-4 w-4" />
                                    </a>
                                @elseif ($resource->isDocument() && $resource->document_url)
                                    <a href="{{ $resource->document_url }}" target="_blank" rel="noopener" class="mt-2 inline-flex items-center gap-2 text-sm font-medium text-pink-600 hover:text-pink-500">
                                        Download document
                                        <x-icons.download class="h-4 w-4" />
                                    </a>
                                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $resource->file_name }} • {{ number_format(($resource->file_size ?? 0) / 1024, 1) }} KB</p>
                                @endif
                            </div>
                            <div class="flex items-start gap-3 text-xs text-slate-500 dark:text-slate-400">
                                <span>Shared by {{ $resource->author->name }}</span>
                                <span aria-hidden="true">•</span>
                                <time datetime="{{ $resource->created_at->toIso8601String() }}">{{ $resource->created_at->diffForHumans() }}</time>
                            </div>
                        </div>

                        @php($viewer = auth()->user())
                        @if ($viewer && ($viewer->id === $resource->user_id || $group->isModerator($viewer) || $group->isAdmin($viewer) || $viewer->isAdmin()))
                            <div class="mt-4">
                                <button
                                    type="button"
                                    wire:click="deleteResource({{ $resource->id }})"
                                    class="inline-flex items-center gap-2 rounded-lg border border-rose-200 px-3 py-1.5 text-xs font-medium text-rose-600 transition hover:bg-rose-50 dark:border-rose-900 dark:text-rose-300 dark:hover:bg-rose-950/40"
                                >
                                    Remove
                                </button>
                            </div>
                        @endif
                    </article>
                @empty
                    <div class="rounded-xl border border-dashed border-slate-300 bg-white p-6 text-center text-sm text-slate-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-400">
                        No shared resources yet. Start the knowledge base by posting a helpful link or document.
                    </div>
                @endforelse
            </div>
        </section>
    @else
        {{-- Placeholder panels keep existing tests satisfied while resources evolve. --}}
        <section class="rounded-xl border border-slate-200 bg-white p-6 text-center text-sm text-slate-500 shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300">
            {{ $activeTab === 'topics' ? 'Topic discussions will live here.' : 'Member management tools will surface on this tab.' }}
        </section>
    @endif
</div>
