<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\Friendship;
use App\Models\Post;
use App\Models\Reaction;
use App\Models\Share;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AccountController extends Controller
{
    /**
     * Deactivate the user's account
     */
    public function deactivate(Request $request)
    {
        $request->validate([
            'password' => 'required|current_password',
        ]);

        $user = Auth::user();
        $user->update([
            'deactivated_at' => Carbon::now(),
        ]);

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('status', 'Your account has been deactivated.');
    }

    /**
     * Permanently delete the user's account
     */
    public function delete(Request $request)
    {
        $request->validate([
            'password' => 'required|current_password',
        ]);

        $user = Auth::user();

        // Delete related data
        $user->profile()->delete();
        $user->posts()->delete();
        $user->comments()->delete();
        $user->reactions()->delete();
        $user->sentMessages()->delete();
        $user->receivedMessages()->delete();
        $user->shares()->delete();
        $user->sentFriendRequests()->delete();
        $user->receivedFriendRequests()->delete();
        $user->activityLogs()->delete();

        // Delete the user
        $user->delete();

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('status', 'Your account has been permanently deleted.');
    }

    /**
     * Reactivate a deactivated account
     */
    public function reactivate(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return back()->withErrors([
                'email' => 'The provided credentials are incorrect.',
            ]);
        }

        if (! $user->deactivated_at) {
            return back()->withErrors([
                'email' => 'This account is not deactivated.',
            ]);
        }

        $user->update([
            'deactivated_at' => null,
        ]);

        Auth::login($user);

        return redirect()->route('dashboard')->with('status', 'Your account has been reactivated.');
    }

    /**
     * Update the user's password
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|current_password',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = Auth::user();
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('status', 'Password updated successfully.');
    }

    /**
     * Show personalised analytics for the authenticated account.
     */
    public function analytics(Request $request): View
    {
        // Resolve the authenticated user once so we can reuse it below.
        $user = $request->user();

        // Capture reusable timestamps to avoid recalculating them repeatedly.
        $now = Carbon::now();
        $lastThirtyDays = $now->copy()->subDays(30);
        $trendWindowStart = $now->copy()->startOfMonth()->subMonths(5);

        // Gather the IDs for the user's posts to simplify the downstream queries.
        $postIds = Post::where('user_id', $user->id)->pluck('id');
        $totalPosts = $postIds->count();

        // Aggregate engagement metrics across comments, reactions, and shares.
        $commentCounts = DB::table('comments')
            ->select('post_id', DB::raw('count(*) as total'))
            ->whereIn('post_id', $postIds)
            ->groupBy('post_id')
            ->pluck('total', 'post_id');

        $reactionCounts = Reaction::query()
            ->select('post_id', DB::raw('count(*) as total'))
            ->whereIn('post_id', $postIds)
            ->groupBy('post_id')
            ->pluck('total', 'post_id');

        $shareCounts = Share::query()
            ->select('post_id', DB::raw('count(*) as total'))
            ->whereIn('post_id', $postIds)
            ->groupBy('post_id')
            ->pluck('total', 'post_id');

        $totalComments = $commentCounts->sum();
        $totalReactions = $reactionCounts->sum();
        $totalShares = $shareCounts->sum();
        $overallInteractions = $totalComments + $totalReactions + $totalShares;
        $averageInteractionsPerPost = $totalPosts > 0
            ? round($overallInteractions / $totalPosts, 1)
            : 0.0;

        // Slice the same metrics for the most recent 30-day window.
        $recentPosts = Post::where('user_id', $user->id)
            ->where('created_at', '>=', $lastThirtyDays)
            ->count();

        $recentComments = DB::table('comments')
            ->whereIn('post_id', $postIds)
            ->where('created_at', '>=', $lastThirtyDays)
            ->count();

        $recentReactions = Reaction::query()
            ->whereIn('post_id', $postIds)
            ->where('created_at', '>=', $lastThirtyDays)
            ->count();

        $recentShares = Share::query()
            ->whereIn('post_id', $postIds)
            ->where('created_at', '>=', $lastThirtyDays)
            ->count();

        $recentAverageInteractions = $recentPosts > 0
            ? round(($recentComments + $recentReactions + $recentShares) / $recentPosts, 1)
            : 0.0;

        $engagement = [
            'total_posts' => $totalPosts,
            'total_comments' => $totalComments,
            'total_reactions' => $totalReactions,
            'total_shares' => $totalShares,
            'overall_interactions' => $overallInteractions,
            'average_per_post' => $averageInteractionsPerPost,
            'last_30_days' => [
                'posts' => $recentPosts,
                'comments' => $recentComments,
                'reactions' => $recentReactions,
                'shares' => $recentShares,
                'average' => $recentAverageInteractions,
            ],
        ];

        // Build monthly trend data so the UI can render a simple timeline.
        $postsForTrend = Post::where('user_id', $user->id)
            ->where('created_at', '>=', $trendWindowStart)
            ->get(['id', 'created_at']);

        $commentsForTrend = DB::table('comments')
            ->whereIn('post_id', $postIds)
            ->where('created_at', '>=', $trendWindowStart)
            ->get(['post_id', 'created_at']);

        $reactionsForTrend = Reaction::query()
            ->whereIn('post_id', $postIds)
            ->where('created_at', '>=', $trendWindowStart)
            ->get(['post_id', 'created_at']);

        $sharesForTrend = Share::query()
            ->whereIn('post_id', $postIds)
            ->where('created_at', '>=', $trendWindowStart)
            ->get(['post_id', 'created_at']);

        $postsByMonth = $postsForTrend
            ->groupBy(fn ($post) => $post->created_at->format('Y-m'))
            ->map->count();

        $commentsByMonth = $commentsForTrend
            ->groupBy(fn ($comment) => Carbon::parse($comment->created_at)->format('Y-m'))
            ->map(fn ($items) => $items->count());

        $reactionsByMonth = $reactionsForTrend
            ->groupBy(fn ($reaction) => Carbon::parse($reaction->created_at)->format('Y-m'))
            ->map(fn ($items) => $items->count());

        $sharesByMonth = $sharesForTrend
            ->groupBy(fn ($share) => Carbon::parse($share->created_at)->format('Y-m'))
            ->map(fn ($items) => $items->count());

        $trendData = collect();

        for ($i = 0; $i < 6; $i++) {
            $periodStart = $trendWindowStart->copy()->addMonths($i);
            $periodKey = $periodStart->format('Y-m');

            $trendData->push([
                'label' => $periodStart->format('M Y'),
                'posts' => $postsByMonth->get($periodKey, 0),
                'comments' => $commentsByMonth->get($periodKey, 0),
                'reactions' => $reactionsByMonth->get($periodKey, 0),
                'shares' => $sharesByMonth->get($periodKey, 0),
            ]);
        }

        // Summarise the state of the user's friendships for the insights section.
        $friendshipQuery = Friendship::query()
            ->where(function ($query) use ($user) {
                $query->where('sender_id', $user->id)
                    ->orWhere('recipient_id', $user->id);
            });

        $totalFriends = (clone $friendshipQuery)->accepted()->count();
        $newFriends = (clone $friendshipQuery)->accepted()
            ->where('accepted_at', '>=', $lastThirtyDays)
            ->count();
        $pendingSent = Friendship::query()
            ->where('sender_id', $user->id)
            ->pending()
            ->count();
        $pendingReceived = Friendship::query()
            ->where('recipient_id', $user->id)
            ->pending()
            ->count();
        $blockedFriends = (clone $friendshipQuery)->blocked()->count();

        $categoryBreakdown = (clone $friendshipQuery)->accepted()
            ->select('category', DB::raw('count(*) as total'))
            ->groupBy('category')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => [
                'category' => $row->category,
                'total' => (int) $row->total,
            ])
            ->values();

        $recentConnections = (clone $friendshipQuery)->accepted()
            ->with(['sender', 'recipient'])
            ->orderByDesc('accepted_at')
            ->take(5)
            ->get()
            ->map(function (Friendship $friendship) use ($user) {
                $friend = $friendship->sender_id === $user->id
                    ? $friendship->recipient
                    : $friendship->sender;

                $since = $friendship->accepted_at ?? $friendship->created_at;

                return [
                    'name' => $friend?->name,
                    'since' => $since ? $since->diffForHumans() : null,
                ];
            })
            ->filter(fn ($connection) => ! empty($connection['name']))
            ->values();

        $friendStats = [
            'total_friends' => $totalFriends,
            'new_friends_last_30_days' => $newFriends,
            'pending_sent' => $pendingSent,
            'pending_received' => $pendingReceived,
            'blocked' => $blockedFriends,
            'category_breakdown' => $categoryBreakdown,
            'recent_connections' => $recentConnections,
        ];

        // Prepare content performance insights such as high performing posts.
        $topPosts = Post::whereIn('id', $postIds)
            ->get(['id', 'content', 'created_at'])
            ->map(function (Post $post) use ($commentCounts, $reactionCounts, $shareCounts) {
                $comments = (int) $commentCounts->get($post->id, 0);
                $reactions = (int) $reactionCounts->get($post->id, 0);
                $shares = (int) $shareCounts->get($post->id, 0);
                $score = $comments + $reactions + $shares;

                return [
                    'id' => $post->id,
                    'content' => $post->content,
                    'created_at' => $post->created_at,
                    'comments' => $comments,
                    'reactions' => $reactions,
                    'shares' => $shares,
                    'engagement_score' => $score,
                ];
            })
            ->sortByDesc('engagement_score')
            ->take(5)
            ->values();

        $contentPerformance = [
            'recent_average_engagement' => $recentAverageInteractions,
            'recent_posting_frequency' => $recentPosts > 0 ? round($recentPosts / 4, 1) : 0.0,
            'overall_interactions' => $overallInteractions,
            'top_posts' => $topPosts,
        ];

        return view('account.analytics', [
            'user' => $user,
            'engagement' => $engagement,
            'trendData' => $trendData,
            'friendStats' => $friendStats,
            'contentPerformance' => $contentPerformance,
        ]);
    }
}
