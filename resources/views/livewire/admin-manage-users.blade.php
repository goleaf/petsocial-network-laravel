<div class="max-w-4xl mx-auto bg-white p-6 rounded-lg shadow">
    <h1 class="text-2xl font-bold mb-6 text-center">Manage Users</h1>
    <table class="w-full border-collapse">
        <thead>
        <tr class="bg-gray-100">
            <th class="p-3 text-left">Name</th>
            <th class="p-3 text-left">Email</th>
            <th class="p-3 text-left">Role</th>
            <th class="p-3 text-left">Status</th>
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
                    <td class="p-3">{{ $user->isBanned() ? 'Banned' : 'Active' }}</td>
                    <td class="p-3 flex space-x-2">
                        <button wire:click="editUser({{ $user->id }})" class="text-blue-500 hover:underline">Edit</button>
                        <button wire:click="deleteUser({{ $user->id }})" class="text-red-500 hover:underline">Delete</button>
                        @if ($user->isBanned())
                            <button wire:click="unbanUser({{ $user->id }})" class="text-green-500 hover:underline">Unban</button>
                        @else
                            <button wire:click="banUser({{ $user->id }})" class="text-orange-500 hover:underline">Ban</button>
                        @endif
                    </td>
                </tr>
            @endif
        @endforeach
        </tbody>
    </table>

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
