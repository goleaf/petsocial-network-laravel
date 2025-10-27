{{-- Dedicated topic card partial so the index stays tidy and reusable. --}}
@php use Illuminate\Support\Str; @endphp
@php
    $poll = $topic->poll;
    $user = auth()->user();
    $userVoteIds = ($poll && $user) ? $poll->votesForUser($user->id) : [];
    $activeSelections = $poll ? ($pollSelections[$poll->id] ?? $userVoteIds) : [];
    $hasVoted = !empty($userVoteIds);
    $pollClosed = $poll ? $poll->hasExpired() : false;
    $canVote = $poll ? ($isGroupMember && !$pollClosed) : false;
    $showResults = $poll ? ($pollClosed || $hasVoted) : false;
@endphp

<article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:shadow-md dark:border-slate-700 dark:bg-slate-900" data-topic-id="{{ $topic->id }}">
    {{-- Header summarises the topic at a glance. --}}
    <header class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
        <div class="space-y-1">
            <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ $topic->title }}</h3>
            <p class="text-sm text-slate-600 dark:text-slate-400">{{ Str::limit(strip_tags($topic->content), 160) }}</p>
        </div>
        <div class="flex flex-wrap items-center gap-2 text-xs font-medium text-slate-500 dark:text-slate-400">
            <span class="rounded-full bg-slate-100 px-3 py-1 dark:bg-slate-800">{{ $topic->created_at?->diffForHumans() ?? 'Recently posted' }}</span>
            <span class="rounded-full bg-slate-100 px-3 py-1 dark:bg-slate-800">{{ $topic->replies_count }} replies</span>
            @if ($poll)
                <span class="rounded-full bg-emerald-100 px-3 py-1 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">Group poll</span>
            @endif
        </div>
    </header>

    @if ($poll)
        {{-- Interactive poll section allows members to cast votes inline. --}}
        <section class="mt-6 space-y-4" aria-labelledby="poll-heading-{{ $poll->id }}">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                <h4 id="poll-heading-{{ $poll->id }}" class="text-base font-semibold text-slate-900 dark:text-slate-100">{{ $poll->question }}</h4>
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    @if ($pollClosed)
                        Poll closed
                    @elseif($poll->expires_at)
                        Closes {{ $poll->expires_at->diffForHumans() }}
                    @else
                        No scheduled close date
                    @endif
                </p>
            </div>

            <form wire:submit.prevent="submitPollVote({{ $poll->id }})" class="space-y-4">
                <div class="space-y-3">
                    @foreach ($poll->options as $option)
                        @php
                            $isSelected = in_array($option->id, $activeSelections, true);
                            $percentage = $option->percentage;
                            $optionVotes = $option->vote_count;
                        @endphp

                        <div class="space-y-1" wire:key="poll-{{ $poll->id }}-option-{{ $option->id }}">
                            <button
                                type="button"
                                wire:click="togglePollOptionSelection({{ $poll->id }}, {{ $option->id }})"
                                @class([
                                    'flex w-full items-center justify-between rounded-xl border px-4 py-3 text-left transition focus:outline-none focus:ring-2 focus:ring-emerald-500 disabled:cursor-not-allowed disabled:opacity-60',
                                    'border-emerald-500 bg-emerald-50 text-emerald-900 dark:border-emerald-400 dark:bg-emerald-900/30 dark:text-emerald-100' => $isSelected,
                                    'border-slate-200 bg-white text-slate-700 hover:border-emerald-400 hover:bg-emerald-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:border-emerald-300 dark:hover:bg-emerald-900/20' => !$isSelected,
                                ])
                                @disabled(!$canVote)
                            >
                                <span class="text-sm font-medium">{{ $option->text }}</span>
                                @if ($showResults)
                                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">{{ $optionVotes }} votes · {{ number_format($percentage, 1) }}%</span>
                                @endif
                            </button>

                            @if ($showResults)
                                <div class="h-2 rounded-full bg-slate-200 dark:bg-slate-700">
                                    <div class="h-2 rounded-full bg-emerald-500 dark:bg-emerald-400" style="width: {{ $percentage }}%;"></div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                @error('pollSelections.' . $poll->id)
                    <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror

                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        {{ $poll->allow_multiple ? 'Select every option that applies to you.' : 'Choose a single option to cast your vote.' }}
                    </p>
                    <button
                        type="submit"
                        class="inline-flex items-center justify-center rounded-full bg-emerald-600 px-5 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 disabled:cursor-not-allowed disabled:opacity-60"
                        @disabled(!$canVote)
                    >
                        @if ($pollClosed)
                            Poll closed
                        @else
                            Submit vote
                        @endif
                    </button>
                </div>
            </form>

            <footer class="flex flex-wrap items-center gap-3 text-xs text-slate-500 dark:text-slate-400">
                <span>Total votes: {{ $poll->total_votes }}</span>
                @if (!$isGroupMember)
                    <span>Join the group to participate in polls.</span>
                @elseif(!$canVote && !$pollClosed)
                    <span>Votes are limited to active members.</span>
                @endif
            </footer>
        </section>
    @endif
</article>
