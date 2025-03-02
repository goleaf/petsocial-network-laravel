<div>
    <h1 class="text-2xl font-bold mb-4">Analytics</h1>
    <div class="grid grid-cols-2 gap-4">
        <div class="bg-white p-4 rounded-lg shadow">
            <h2 class="text-lg font-semibold">Total Users</h2>
            <p class="text-2xl">{{ $userCount }}</p>
        </div>
        <div class="bg-white p-4 rounded-lg shadow">
            <h2 class="text-lg font-semibold">Total Posts</h2>
            <p class="text-2xl">{{ $postCount }}</p>
        </div>
        <div class="bg-white p-4 rounded-lg shadow">
            <h2 class="text-lg font-semibold">Total Comments</h2>
            <p class="text-2xl">{{ $commentCount }}</p>
        </div>
        <div class="bg-white p-4 rounded-lg shadow">
            <h2 class="text-lg font-semibold">Total Reactions</h2>
            <p class="text-2xl">{{ $reactionCount }}</p>
        </div>
        <div class="bg-white p-4 rounded-lg shadow">
            <h2 class="text-lg font-semibold">Total Shares</h2>
            <p class="text-2xl">{{ $shareCount }}</p>
        </div>
    </div>
    <div class="grid grid-cols-2 gap-4">
        <!-- Existing stats -->
        <div class="bg-white p-4 rounded-lg shadow">
            <h2 class="text-lg font-semibold">Total Friendships</h2>
            <p class="text-2xl">{{ $friendCount }}</p>
        </div>
    </div>

    <div class="mt-6 bg-white p-4 rounded-lg shadow">
        <h2 class="text-lg font-semibold mb-2">Top 5 Active Users</h2>
        <ul>
            @foreach ($topUsers as $user)
                <li>{{ $user->name }} ({{ $user->posts_count }} posts)</li>
            @endforeach
        </ul>
    </div>
</div>
