<div class="max-w-4xl mx-auto bg-white p-6 rounded-lg shadow">
    <h1 class="text-2xl font-bold mb-6 text-center">Manage Users</h1>
    <table class="w-full border-collapse">
        <thead>
        <tr class="bg-gray-100">
            <th class="p-3 text-left">Name</th>
            <th class="p-3 text-left">Email</th>
            <th class="p-3 text-left">Role</th>
            <th class="p-3 text-left">Status</th>
            <th class="p-3 text-left">Activity</th>
            <th class="p-3 text-left">Actions</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($users as $user)
            @if ($editingUserId === $user->id)
                <tr>
                    <td class="p-3">
                        <input type="text" wire:model="editName" class="w-full p-2 border rounded">
                    </td>
                    <td class="p-3">
                        <input type="email" wire:model="editEmail" class="w-full p-2 border rounded">
                    </td>
                    <td class="p-3">
                        <select wire:model="editRole" class="w-full p-2 border rounded">
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                        </select>
                    </td>
                    <td class="p-3">{{ $user->isBanned() ? 'Banned' : 'Active' }}</td>
                    <td class="p-3 flex space-x-2">
                        <button wire:click="updateUser" class="text-green-500 hover:underline">Save</button>
                        <button wire:click="cancelEdit" class="text-gray-500 hover:underline">Cancel</button>
                    </td>
                </tr>
            @else
                <tr>
                    <td class="p-3">{{ $user->name }}</td>
                    <td class="p-3">{{ $user->email }}</td>
                    <td class="p-3">{{ $user->role }}</td>
                    <td class="p-3">
                        @if ($user->isSuspended())
                            Suspended until {{ $user->suspension_ends_at ? $user->suspension_ends_at->diffForHumans() : 'indefinitely' }}
                            <p class="text-sm text-gray-500">Reason: {{ $user->suspension_reason }}</p>
                        @else
                            Active
                        @endif
                    </td>
                    <td class="p-3">{{ $user->activity_logs_count }} actions</td>
                    <td class="p-3 flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2">
                        <button wire:click="editUser({{ $user->id }})" class="text-blue-500 hover:underline">Edit</button>
                        <button wire:click="deleteUser({{ $user->id }})" class="text-red-500 hover:underline">Delete</button>
                        @if ($user->isSuspended())
                            <button wire:click="unsuspendUser({{ $user->id }})" class="text-green-500 hover:underline">Unsuspend</button>
                        @else
                            <button wire:click="suspendUser({{ $user->id }})" class="text-orange-500 hover:underline">Suspend</button>
                        @endif
                    </td>
                </tr>
            @endif
        @endforeach
        </tbody>
    </table>

    @if ($suspendUserId)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center">
            <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md">
                <h2 class="text-xl font-semibold mb-4">Suspend User</h2>
                <form wire:submit.prevent="confirmSuspend">
                    <div class="mb-4">
                        <label class="block text-gray-700 font-semibold mb-2">Suspension Duration (days, leave blank for indefinite)</label>
                        <input type="number" wire:model="suspendDays" class="w-full p-3 border rounded-lg">
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 font-semibold mb-2">Reason</label>
                        <textarea wire:model="suspendReason" class="w-full p-3 border rounded-lg" rows="3" placeholder="Why is this user being suspended?"></textarea>
                    </div>
                    <div class="flex space-x-2">
                        <button type="submit" class="bg-orange-500 text-white px-4 py-2 rounded-lg hover:bg-orange-600">Confirm</button>
                        <button wire:click="$set('suspendUserId', null)" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <h2 class="text-xl font-semibold mt-8 mb-2">Reported Posts</h2>
    @foreach ($reportedPosts as $post)
        <div class="bg-gray-50 p-4 rounded-lg mb-4">
            <p>{{ $post->content }} by {{ $post->user->name }}</p>
            <ul class="list-disc ml-6">
                @foreach ($post->reports as $report)
                    <li>{{ $report->reason }} (by {{ $report->user->name }})</li>
                @endforeach
            </ul>
            <button wire:click="deletePost({{ $post->id }})" class="text-red-500 hover:underline mt-2">Delete Post</button>
        </div>
    @endforeach

    <h2 class="text-xl font-semibold mt-8 mb-2">Reported Comments</h2>
    @foreach ($reportedComments as $comment)
        <div class="bg-gray-50 p-4 rounded-lg mb-4">
            <p>{{ $comment->content }} by {{ $comment->user->name }}</p>
            <ul class="list-disc ml-6">
                @foreach ($comment->reports as $report)
                    <li>{{ $report->reason }} (by {{ $report->user->name }})</li>
                @endforeach
            </ul>
            <button wire:click="deleteComment({{ $comment->id }})" class="text-red-500 hover:underline mt-2">Delete Comment</button>
        </div>
    @endforeach
</div>
