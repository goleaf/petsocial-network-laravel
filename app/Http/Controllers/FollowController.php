<?php

namespace App\Http\Controllers;

use App\Models\Follow;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FollowController extends Controller
{
    /**
     * Display a listing of the user's followers and following.
     */
    public function index()
    {
        $user = Auth::user();
        
        $followers = $user->followers()->paginate(20, ['*'], 'followers_page');
        $following = $user->following()->paginate(20, ['*'], 'following_page');
        
        return view('follows.index', compact('followers', 'following'));
    }

    /**
     * Follow a user.
     */
    public function follow(User $user)
    {
        $currentUser = Auth::user();
        
        // Check if already following
        if ($currentUser->isFollowing($user)) {
            return back()->with('error', 'You are already following this user.');
        }
        
        // Create follow relationship
        $follow = Follow::create([
            'follower_id' => $currentUser->id,
            'followed_id' => $user->id,
            'notify' => true,
        ]);
        
        // Create a notification for the followed user
        $user->notifications()->create([
            'type' => 'new_follower',
            'notifiable_type' => User::class,
            'notifiable_id' => $currentUser->id,
            'data' => [
                'message' => "{$currentUser->name} started following you",
                'follow_id' => $follow->id,
            ],
            'priority' => 'normal',
        ]);
        
        return back()->with('success', 'You are now following ' . $user->name);
    }

    /**
     * Unfollow a user.
     */
    public function unfollow(User $user)
    {
        $currentUser = Auth::user();
        
        // Check if following
        if (!$currentUser->isFollowing($user)) {
            return back()->with('error', 'You are not following this user.');
        }
        
        // Delete follow relationship
        Follow::where('follower_id', $currentUser->id)
              ->where('followed_id', $user->id)
              ->delete();
        
        return back()->with('success', 'You have unfollowed ' . $user->name);
    }

    /**
     * Toggle notification settings for a followed user.
     */
    public function toggleNotifications(User $user)
    {
        $currentUser = Auth::user();
        
        // Check if following
        if (!$currentUser->isFollowing($user)) {
            return back()->with('error', 'You are not following this user.');
        }
        
        // Get the follow relationship
        $follow = Follow::where('follower_id', $currentUser->id)
                        ->where('followed_id', $user->id)
                        ->first();
        
        // Toggle notifications
        $follow->toggleNotifications();
        
        $status = $follow->notify ? 'enabled' : 'disabled';
        return back()->with('success', "Notifications for {$user->name} have been {$status}.");
    }

    /**
     * View a specific user's followers.
     */
    public function followers(User $user)
    {
        // Check visibility permissions
        if ($user->profile_visibility === 'private' && !Auth::user()->isFriendWith($user) && Auth::id() !== $user->id) {
            return back()->with('error', 'This profile is private.');
        }
        
        $followers = $user->followers()->paginate(20);
        
        return view('follows.followers', compact('user', 'followers'));
    }

    /**
     * View a specific user's following.
     */
    public function following(User $user)
    {
        // Check visibility permissions
        if ($user->profile_visibility === 'private' && !Auth::user()->isFriendWith($user) && Auth::id() !== $user->id) {
            return back()->with('error', 'This profile is private.');
        }
        
        $following = $user->following()->paginate(20);
        
        return view('follows.following', compact('user', 'following'));
    }

    /**
     * Get friend recommendations based on location.
     */
    public function recommendations()
    {
        $user = Auth::user();
        
        // Get users with the same location
        $recommendations = User::where('id', '!=', $user->id)
                               ->where('location', $user->location)
                               ->whereNotIn('id', function ($query) use ($user) {
                                   // Exclude users who are already friends
                                   $query->select('sender_id')
                                         ->from('friendships')
                                         ->where('recipient_id', $user->id)
                                         ->where('status', 'accepted')
                                         ->union(
                                             $query->newQuery()
                                                  ->select('recipient_id')
                                                  ->from('friendships')
                                                  ->where('sender_id', $user->id)
                                                  ->where('status', 'accepted')
                                         );
                               })
                               ->whereNotIn('id', function ($query) use ($user) {
                                   // Exclude users who are already being followed
                                   $query->select('followed_id')
                                         ->from('follows')
                                         ->where('follower_id', $user->id);
                               })
                               ->limit(10)
                               ->get();
        
        return view('follows.recommendations', compact('recommendations'));
    }
}
