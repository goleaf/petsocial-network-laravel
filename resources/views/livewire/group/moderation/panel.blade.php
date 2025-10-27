<div class="space-y-6">
    {{-- Moderation dashboard surfaces actionable membership queues for group operators. --}}
    <header class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $group->name }} Moderation</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400">Review requests, manage members, and keep the community healthy.</p>
        </div>
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                <span>Status</span>
                <select wire:model="statusFilter" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-pink-500 focus:ring-pink-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
                    <option value="pending">Pending</option>
                    <option value="active">Active</option>
                    <option value="banned">Banned</option>
                </select>
            </label>
            <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M8 4a4 4 0 102.817 6.853l3.165 3.165a.75.75 0 101.06-1.06l-3.165-3.165A4 4 0 008 4zM5.5 8a2.5 2.5 0 115 0 2.5 2.5 0 01-5 0z" clip-rule="evenodd" />
                    </svg>
                </span>
                <input
                    wire:model.debounce.500ms="search"
                    type="search"
                    placeholder="Search members"
                    class="w-full rounded-md border border-gray-300 py-2 pl-9 pr-3 text-sm shadow-sm focus:border-pink-500 focus:ring-pink-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
                />
            </div>
        </div>
    </header>

    <section class="grid gap-4 sm:grid-cols-3">
        {{-- Metric tiles give moderators quick insight into queue sizes. --}}
        <article class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <p class="text-sm text-gray-500 dark:text-gray-400">Pending Requests</p>
            <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $metrics['pending'] }}</p>
        </article>
        <article class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <p class="text-sm text-gray-500 dark:text-gray-400">Active Members</p>
            <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $metrics['active'] }}</p>
        </article>
        <article class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <p class="text-sm text-gray-500 dark:text-gray-400">Banned Members</p>
            <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $metrics['banned'] }}</p>
        </article>
    </section>

    <section class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
        {{-- Member table surfaces contextual actions for each moderation state. --}}
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-800">
                <tr>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">Member</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">Status</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">Joined</th>
                    <th scope="col" class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900">
                @forelse ($members as $member)
                    <tr>
                        <td class="px-4 py-3">
                            <div class="flex flex-col">
                                <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $member->name }}</span>
                                <span class="text-xs text-gray-500 dark:text-gray-400">{{ $member->email }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-sm capitalize text-gray-700 dark:text-gray-300">{{ $member->pivot->status }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                            {{ optional($member->pivot->joined_at)->format('M j, Y') ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex flex-wrap justify-end gap-2">
                                @if ($member->pivot->status === 'pending')
                                    <button wire:click="approveMember({{ $member->id }})" class="rounded-md bg-green-600 px-3 py-1 text-xs font-semibold text-white shadow-sm hover:bg-green-500 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900">Approve</button>
                                    <button wire:click="denyMember({{ $member->id }})" class="rounded-md bg-gray-200 px-3 py-1 text-xs font-semibold text-gray-700 hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 dark:focus:ring-offset-gray-900">Deny</button>
                                @elseif ($member->pivot->status === 'active')
                                    <button wire:click="banMember({{ $member->id }})" class="rounded-md bg-red-600 px-3 py-1 text-xs font-semibold text-white shadow-sm hover:bg-red-500 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900">Ban</button>
                                    <button wire:click="removeMember({{ $member->id }})" class="rounded-md bg-gray-200 px-3 py-1 text-xs font-semibold text-gray-700 hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 dark:focus:ring-offset-gray-900">Remove</button>
                                @elseif ($member->pivot->status === 'banned')
                                    <button wire:click="unbanMember({{ $member->id }})" class="rounded-md bg-yellow-500 px-3 py-1 text-xs font-semibold text-gray-900 shadow-sm hover:bg-yellow-400 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:ring-offset-2 dark:focus:ring-offset-gray-900">Unban</button>
                                    <button wire:click="removeMember({{ $member->id }})" class="rounded-md bg-gray-200 px-3 py-1 text-xs font-semibold text-gray-700 hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 dark:focus:ring-offset-gray-900">Remove</button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400">No members found for the selected filters.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>

    <footer class="flex items-center justify-between">
        {{-- Livewire pagination component keeps navigation consistent with the design system. --}}
        <p class="text-sm text-gray-600 dark:text-gray-400">Showing {{ $members->firstItem() ?? 0 }}–{{ $members->lastItem() ?? 0 }} of {{ $members->total() }} members</p>
        {{ $members->links() }}
    </footer>
</div>
