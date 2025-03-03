<div>
    <h1 class="text-2xl font-bold mb-4">{{ __('admin.analytics') }}</h1>
    <div class="grid grid-cols-2 gap-4">
        <div class="bg-white p-4 rounded-lg shadow">
            <h2 class="text-lg font-semibold">{{ __('admin.total_users') }}</h2>
            <p class="text-2xl">{{ $userCount }}</p>
        </div>
        <div class="bg-white p-4 rounded-lg shadow">
            <h2 class="text-lg font-semibold">{{ __('admin.total_posts') }}</h2>
            <p class="text-2xl">{{ $postCount }}</p>
        </div>
        <div class="bg-white p-4 rounded-lg shadow">
            <h2 class="text-lg font-semibold">{{ __('admin.total_comments') }}</h2>
            <p class="text-2xl">{{ $commentCount }}</p>
        </div>
        <div class="bg-white p-4 rounded-lg shadow">
            <h2 class="text-lg font-semibold">{{ __('admin.total_reactions') }}</h2>
            <p class="text-2xl">{{ $reactionCount }}</p>
        </div>
        <div class="bg-white p-4 rounded-lg shadow">
            <h2 class="text-lg font-semibold">{{ __('admin.total_shares') }}</h2>
            <p class="text-2xl">{{ $shareCount }}</p>
        </div>
    </div>
    <div class="grid grid-cols-2 gap-4">
        <!-- Existing stats -->
        <div class="bg-white p-4 rounded-lg shadow">
            <h2 class="text-lg font-semibold">{{ __('admin.total_friendships') }}</h2>
            <p class="text-2xl">{{ $friendCount }}</p>
        </div>
    </div>

    <div class="mt-6 bg-white p-4 rounded-lg shadow">
        <h2 class="text-lg font-semibold mb-2">{{ __('admin.top_active_users') }}</h2>
        <ul>
            @foreach ($topUsers as $user)
                <li>{{ __('admin.user_post_count', ['name' => $user->name, 'count' => $user->posts_count]) }}</li>
            @endforeach
        </ul>
    </div>
</div>
