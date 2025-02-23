<div>
    <h1>Manage Users</h1>
    <table>
        <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Actions</th></tr></thead>
        <tbody>
        @foreach ($users as $user)
            <tr><td>{{ $user->name }}</td><td>{{ $user->email }}</td><td>{{ $user->role }}</td>
                <td><button wire:click="deleteUser({{ $user->id }})">Delete</button></td></tr>
        @endforeach
        </tbody>
    </table>

    <h2>Reported Posts</h2>
    @foreach ($reportedPosts as $post)
        <div>
            <p>{{ $post->content }} by {{ $post->user->name }}</p>
            <ul>
                @foreach ($post->reports as $report)
                    <li>{{ $report->reason }} (by {{ $report->user->name }})</li>
                @endforeach
            </ul>
            <button wire:click="deletePost({{ $post->id }})">Delete Post</button>
        </div>
    @endforeach

    <h2>Reported Comments</h2>
    @foreach ($reportedComments as $comment)
        <div>
            <p>{{ $comment->content }} by {{ $comment->user->name }}</p>
            <ul>
                @foreach ($comment->reports as $report)
                    <li>{{ $report->reason }} (by {{ $report->user->name }})</li>
                @endforeach
            </ul>
            <button wire:click="deleteComment({{ $comment->id }})">Delete Comment</button>
        </div>
    @endforeach
</div>
