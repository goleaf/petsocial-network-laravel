<div class="flex">
    <div class="w-1/4 bg-gray-200 p-4">
        <h2 class="font-bold text-lg">{{ __('admin.navigation') }}</h2>
        <ul class="mt-4">
            <li><a href="{{ route('admin.dashboard') }}" class="block py-2 text-blue-600 hover:underline">{{ __('admin.dashboard') }}</a></li>
            <li><a href="{{ route('admin.users') }}" class="block py-2 text-blue-600 hover:underline">{{ __('admin.manage_users') }}</a></li>
            <li><a href="{{ route('admin.analytics') }}" class="block py-2 text-blue-600 hover:underline">{{ __('admin.analytics') }}</a></li>
            <li><a href="{{ route('admin.reports') }}" class="block py-2 text-blue-600 hover:underline">{{ __('admin.reports') }}</a></li>
            <li><a href="{{ route('admin.settings') }}" class="block py-2 text-blue-600 hover:underline">{{ __('admin.settings') }}</a></li>
        </ul>
    </div>
    <div class="w-3/4 p-4">
        <h1 class="text-2xl font-bold mb-4">{{ __('admin.admin_panel') }}</h1>
        <p class="mb-4">{{ __('admin.welcome_admin', ['name' => auth()->user()->name]) }}</p>
        
        <div class="grid grid-cols-2 gap-4">
            <div class="bg-white p-4 rounded-lg shadow">
                <h2 class="text-lg font-semibold mb-2">{{ __('admin.overview') }}</h2>
                <p>{{ __('admin.total_users', ['count' => $totalUsers]) }}</p>
                <p>{{ __('admin.total_pets', ['count' => $totalPets]) }}</p>
                <p>{{ __('admin.total_posts', ['count' => $totalPosts]) }}</p>
            </div>
            <div class="bg-white p-4 rounded-lg shadow">
                <h2 class="text-lg font-semibold mb-2">{{ __('admin.engagement') }}</h2>
                <p>{{ __('admin.total_comments', ['count' => $totalComments]) }}</p>
                <p>{{ __('admin.total_reactions', ['count' => $totalReactions]) }}</p>
                <p>{{ __('admin.total_shares', ['count' => $totalShares]) }}</p>
            </div>
        </div>

        <div class="mt-6 bg-white p-4 rounded-lg shadow">
            <h2 class="text-lg font-semibold mb-2">{{ __('admin.recent_activity') }}</h2>
            <ul>
                @foreach($reportedPosts as $post)
                    <li>
                        {{ $post->user->name }}: {{ Str::limit($post->content, 50) }}
                        <button wire:click="viewPost({{ $post->id }})" class="text-blue-500 hover:underline">{{ __('admin.view') }}</button>
                        <button wire:click="deletePost({{ $post->id }})" class="text-red-500 hover:underline">{{ __('admin.delete') }}</button>
                    </li>
                @endforeach
            </ul>
            <h3 class="font-medium mt-2">{{ __('admin.reported_comments') }}</h3>
            <ul>
                @foreach($reportedComments as $comment)
                    <li>
                        {{ $comment->user->name }}: {{ Str::limit($comment->content, 50) }}
                        <button wire:click="viewPost({{ $comment->post_id }})" class="text-blue-500 hover:underline">{{ __('admin.view') }}</button>
                        <button wire:click="deleteComment({{ $comment->id }})" class="text-red-500 hover:underline">{{ __('admin.delete') }}</button>
                    </li>
                @endforeach
            </ul>
        </div>

        <div class="mt-6 bg-white p-4 rounded-lg shadow">
            <h2 class="text-lg font-semibold mb-2">{{ __('admin.user_management') }}</h2>
            <h3 class="font-medium mt-2">{{ __('admin.recent_users') }}</h3>
            <ul>
                @foreach($recentUsers as $user)
                    <li>
                        {{ $user->name }} ({{ $user->email }})
                        <button wire:click="viewUser({{ $user->id }})" class="text-blue-500 hover:underline">{{ __('admin.view') }}</button>
                    </li>
                @endforeach
            </ul>
            <h3 class="font-medium mt-2">{{ __('admin.suspended_users') }}</h3>
            <ul>
                @foreach($suspendedUsers as $user)
                    <li>
                        {{ $user->name }} ({{ __('admin.suspended_until', ['time' => $user->suspension_ends_at ? $user->suspension_ends_at->format('Y-m-d H:i') : __('admin.indefinitely')]) }})
                        <button wire:click="unsuspendUser({{ $user->id }})" class="text-green-500 hover:underline">{{ __('admin.unsuspend') }}</button>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
