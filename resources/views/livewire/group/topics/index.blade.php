@php use Illuminate\Support\Str; @endphp

{{-- Rich group topics surface that now renders interactive polls for community decisions. --}}
<div class="space-y-12">
    <section class="space-y-6">
        <header class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Pinned topics</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400">Highlights curated by the moderators stay above the fold.</p>
            </div>
            {{-- Preserve the testing hook so automated suites can continue counting pinned topics. --}}
            <span data-testid="pinned-topics-count" class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-700 dark:bg-slate-800 dark:text-slate-300">{{ $pinnedTopics->count() }}</span>
        </header>

        <div class="space-y-6">
            @forelse ($pinnedTopics as $topic)
                @include('livewire.group.topics.partials.topic-card', ['topic' => $topic])
            @empty
                <p class="text-sm text-slate-500 dark:text-slate-400">No pinned discussions yet—check back after moderators highlight a conversation.</p>
            @endforelse
        </div>
    </section>

    <section class="space-y-6">
        <header class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Latest discussions</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400">Collaborate with your fellow members and vote on group decisions.</p>
            </div>
            {{-- Preserve the testing hook for the paginated collection. --}}
            <span data-testid="regular-topics-count" class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-700 dark:bg-slate-800 dark:text-slate-300">{{ $regularTopics->count() }}</span>
        </header>

        <div class="space-y-6">
            @forelse ($regularTopics as $topic)
                @include('livewire.group.topics.partials.topic-card', ['topic' => $topic])
            @empty
                <p class="text-sm text-slate-500 dark:text-slate-400">Be the first to start a conversation or attach a poll to gather quick feedback.</p>
            @endforelse
        </div>

        {{-- Render pagination controls to keep the list navigable. --}}
        <div>
            {{ $regularTopics->links() }}
        </div>
    </section>
</div>
