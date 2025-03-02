<?php

namespace App\Http\Controllers;

use App\Models\Friendship;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FriendshipController extends Controller
{
    /**
     * Display a listing of the user's friendships.
     */
    public function index()
    {
        $user = Auth::user();
        
        $friends = $user->friends()->paginate(20);
        $pendingRequests = $user->pendingReceivedFriendships()->with('sender')->get();
        $sentRequests = $user->pendingSentFriendships()->with('recipient')->get();
        
        return view('friendships.index', compact('friends', 'pendingRequests', 'sentRequests'));
    }

    /**
     * Send a friend request to a user.
     */
    public function sendRequest(User $user)
    {
        $currentUser = Auth::user();
        
        // Check if users are already friends
        if ($currentUser->isFriendWith($user)) {
            return back()->with('error', 'You are already friends with this user.');
        }
        
        // Check if there's a pending request
        if ($currentUser->hasSentPendingFriendRequestTo($user)) {
            return back()->with('error', 'You have already sent a friend request to this user.');
        }
        
        // Check if the other user has sent a request
        if ($currentUser->hasPendingFriendRequestFrom($user)) {
            // Accept their request instead
            $friendship = $currentUser->receivedFriendships()
                ->where('sender_id', $user->id)
                ->where('status', 'pending')
                ->first();
                
            $friendship->accept();
            
            return back()->with('success', 'You are now friends with ' . $user->name);
        }
        
        // Create new friendship request
        $friendship = Friendship::create([
            'sender_id' => $currentUser->id,
            'recipient_id' => $user->id,
            'status' => 'pending',
        ]);
        
        // Create a notification for the recipient
        $user->notifications()->create([
            'type' => 'friend_request',
            'notifiable_type' => User::class,
            'notifiable_id' => $currentUser->id,
            'data' => [
                'message' => "{$currentUser->name} sent you a friend request",
                'friendship_id' => $friendship->id,
            ],
            'priority' => 'normal',
        ]);
        
        return back()->with('success', 'Friend request sent to ' . $user->name);
    }

    /**
     * Accept a friend request.
     */
    public function acceptRequest(Friendship $friendship)
    {
        $currentUser = Auth::user();
        
        // Check if the request is for the current user
        if ($friendship->recipient_id !== $currentUser->id) {
            return back()->with('error', 'You do not have permission to accept this request.');
        }
        
        $friendship->accept();
        
        return back()->with('success', 'Friend request accepted.');
    }

    /**
     * Decline a friend request.
     */
    public function declineRequest(Friendship $friendship)
    {
        $currentUser = Auth::user();
        
        // Check if the request is for the current user
        if ($friendship->recipient_id !== $currentUser->id) {
            return back()->with('error', 'You do not have permission to decline this request.');
        }
        
        $friendship->decline();
        
        return back()->with('success', 'Friend request declined.');
    }

    /**
     * Remove a friend.
     */
    public function removeFriend(User $user)
    {
        $currentUser = Auth::user();
        
        if (!$currentUser->isFriendWith($user)) {
            return back()->with('error', 'You are not friends with this user.');
        }
        
        // Find and delete the friendship
        $friendship = $currentUser->friendships()
            ->where(function ($query) use ($user) {
                $query->where('sender_id', $user->id)
                      ->orWhere('recipient_id', $user->id);
            })
            ->where('status', 'accepted')
            ->first();
            
        if ($friendship) {
            $friendship->delete();
        }
        
        return back()->with('success', 'Friend removed successfully.');
    }

    /**
     * Categorize a friend.
     */
    public function categorize(Request $request, User $user)
    {
        $currentUser = Auth::user();
        
        if (!$currentUser->isFriendWith($user)) {
            return back()->with('error', 'You are not friends with this user.');
        }
        
        $request->validate([
            'category' => 'required|string|max:50',
        ]);
        
        // Find the friendship
        $friendship = $currentUser->friendships()
            ->where(function ($query) use ($user) {
                $query->where('sender_id', $user->id)
                      ->orWhere('recipient_id', $user->id);
            })
            ->where('status', 'accepted')
            ->first();
            
        if ($friendship) {
            $friendship->categorize($request->category);
        }
        
        return back()->with('success', 'Friend categorized successfully.');
    }

    /**
     * Block a user.
     */
    public function blockUser(User $user)
    {
        $currentUser = Auth::user();
        
        // Check if there's an existing friendship
        $friendship = $currentUser->friendships()
            ->where(function ($query) use ($user) {
                $query->where('sender_id', $user->id)
                      ->orWhere('recipient_id', $user->id);
            })
            ->first();
            
        if ($friendship) {
            $friendship->block();
        } else {
            // Create a new blocked friendship
            Friendship::create([
                'sender_id' => $currentUser->id,
                'recipient_id' => $user->id,
                'status' => 'blocked',
            ]);
        }
        
        return back()->with('success', 'User blocked successfully.');
    }

    /**
     * Unblock a user.
     */
    public function unblockUser(User $user)
    {
        $currentUser = Auth::user();
        
        // Find the blocked friendship
        $friendship = $currentUser->friendships()
            ->where(function ($query) use ($user) {
                $query->where('sender_id', $user->id)
                      ->orWhere('recipient_id', $user->id);
            })
            ->where('status', 'blocked')
            ->first();
            
        if ($friendship) {
            $friendship->delete();
        }
        
        return back()->with('success', 'User unblocked successfully.');
    }

    /**
     * Get a list of blocked users.
     */
    public function blockedUsers()
    {
        $user = Auth::user();
        
        $blockedUsers = $user->sentFriendships()
            ->where('status', 'blocked')
            ->with('recipient')
            ->get()
            ->pluck('recipient');
            
        return view('friendships.blocked', compact('blockedUsers'));
    }
}
