<?php

namespace App\Http\Controllers\Social;

use App\Http\Controllers\Controller;
use App\Models\Pet;
use App\Models\User;
use App\Traits\EntityTypeTrait;
use App\Traits\FriendshipTrait;
use App\Traits\ActivityTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UnifiedFriendshipController extends Controller
{
    use EntityTypeTrait, FriendshipTrait, ActivityTrait;
    
    /**
     * Display a listing of the entity's friendships.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $entityType = $request->input('entity_type', 'user');
        $entityId = $request->input('entity_id', Auth::id());
        
        $this->initializeEntity($entityType, $entityId);
        
        // Check authorization
        if (!$this->isAuthorized()) {
            return redirect()->back()->with('error', __('friends.not_authorized'));
        }
        
        $entity = $this->getEntity();
        $friendIds = $this->getFriendIds();
        
        // Get friends with pagination
        $entityModel = $this->getEntityModel();
        $friends = $entityModel::whereIn('id', $friendIds)->paginate(20);
        
        // Get pending requests
        $pendingRequests = $this->getPendingRequests();
        
        // Get sent requests
        $sentRequests = $this->getSentRequests();
        
        return view('friendships.index', compact('entity', 'entityType', 'friends', 'pendingRequests', 'sentRequests'));
    }

    /**
     * Send a friend request to an entity.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\Response
     */
    public function sendRequest(Request $request)
    {
        $entityType = $request->input('entity_type', 'user');
        $entityId = $request->input('entity_id');
        $friendType = $request->input('friend_type', $entityType);
        $friendId = $request->input('friend_id');
        
        $this->initializeEntity($entityType, Auth::id());
        
        // Check authorization
        if (!$this->isAuthorized()) {
            return redirect()->back()->with('error', __('friends.not_authorized'));
        }
        
        // Check if entities are already friends
        if ($this->areFriends($friendId)) {
            return redirect()->back()->with('error', __('friends.already_friends'));
        }
        
        // Send friend request
        $this->addFriend($friendId);
        
        // Log activity
        $this->logActivity('friend_request_sent', [
            'friend_type' => $friendType,
            'friend_id' => $friendId
        ]);
        
        return redirect()->back()->with('success', __('friends.friend_request_sent'));
    }

    /**
     * Accept a friend request.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\Response
     */
    public function acceptRequest(Request $request)
    {
        $entityType = $request->input('entity_type', 'user');
        $entityId = $request->input('entity_id', Auth::id());
        $friendType = $request->input('friend_type', $entityType);
        $friendId = $request->input('friend_id');
        
        $this->initializeEntity($entityType, $entityId);
        
        // Check authorization
        if (!$this->isAuthorized()) {
            return redirect()->back()->with('error', __('friends.not_authorized'));
        }
        
        // Accept friend request
        $result = $this->acceptFriend($friendId);
        
        if ($result) {
            // Log activity
            $this->logActivity('friend_request_accepted', [
                'friend_type' => $friendType,
                'friend_id' => $friendId
            ]);
            
            return redirect()->back()->with('success', __('friends.friend_request_accepted'));
        }
        
        return redirect()->back()->with('error', __('friendships.error_message'));
    }

    /**
     * Decline a friend request.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\Response
     */
    public function declineRequest(Request $request)
    {
        $entityType = $request->input('entity_type', 'user');
        $entityId = $request->input('entity_id', Auth::id());
        $friendId = $request->input('friend_id');
        
        $this->initializeEntity($entityType, $entityId);
        
        // Check authorization
        if (!$this->isAuthorized()) {
            return redirect()->back()->with('error', __('friends.not_authorized'));
        }
        
        // Decline friend request
        $result = $this->declineFriend($friendId);
        
        if ($result) {
            return redirect()->back()->with('success', __('friends.friend_request_declined'));
        }
        
        return redirect()->back()->with('error', __('friendships.error_message'));
    }

    /**
     * Remove a friend.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\Response
     */
    public function removeFriend(Request $request)
    {
        $entityType = $request->input('entity_type', 'user');
        $entityId = $request->input('entity_id', Auth::id());
        $friendId = $request->input('friend_id');
        
        $this->initializeEntity($entityType, $entityId);
        
        // Check authorization
        if (!$this->isAuthorized()) {
            return redirect()->back()->with('error', __('friends.not_authorized'));
        }
        
        // Check if entities are friends
        if (!$this->areFriends($friendId)) {
            return redirect()->back()->with('error', __('friends.not_friends'));
        }
        
        // Remove friend
        $this->removeFriend($friendId);
        
        return redirect()->back()->with('success', __('friends.friend_removed'));
    }

    /**
     * Categorize a friend.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\Response
     */
    public function categorize(Request $request)
    {
        $entityType = $request->input('entity_type', 'user');
        $entityId = $request->input('entity_id', Auth::id());
        $friendId = $request->input('friend_id');
        $category = $request->input('category');
        
        $this->initializeEntity($entityType, $entityId);
        
        // Check authorization
        if (!$this->isAuthorized()) {
            return redirect()->back()->with('error', __('friends.not_authorized'));
        }
        
        // Check if entities are friends
        if (!$this->areFriends($friendId)) {
            return redirect()->back()->with('error', __('friends.not_friends'));
        }
        
        // Categorize friend
        $this->categorizeFriends([$friendId], $category);
        
        return redirect()->back()->with('success', __('friends.category_applied'));
    }

    /**
     * Block an entity.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\Response
     */
    public function blockEntity(Request $request)
    {
        $entityType = $request->input('entity_type', 'user');
        $entityId = $request->input('entity_id', Auth::id());
        $blockType = $request->input('block_type', $entityType);
        $blockId = $request->input('block_id');
        
        $this->initializeEntity($entityType, $entityId);
        
        // Check authorization
        if (!$this->isAuthorized()) {
            return redirect()->back()->with('error', __('friends.not_authorized'));
        }
        
        // Block entity
        $this->blockEntity($blockId);
        
        return redirect()->back()->with('success', __('friends.entity_blocked'));
    }

    /**
     * Unblock an entity.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\Response
     */
    public function unblockEntity(Request $request)
    {
        $entityType = $request->input('entity_type', 'user');
        $entityId = $request->input('entity_id', Auth::id());
        $unblockId = $request->input('unblock_id');
        
        $this->initializeEntity($entityType, $entityId);
        
        // Check authorization
        if (!$this->isAuthorized()) {
            return redirect()->back()->with('error', __('friends.not_authorized'));
        }
        
        // Unblock entity
        $this->unblockEntity($unblockId);
        
        return redirect()->back()->with('success', __('friends.entity_unblocked'));
    }
    
    /**
     * Get pending friend requests for the entity.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function getPendingRequests()
    {
        $friendshipModel = $this->getFriendshipModel();
        $entityIdField = $this->getEntityIdField();
        $friendIdField = $this->getFriendIdField();
        
        return $friendshipModel::where($friendIdField, $this->entityId)
            ->where('status', 'pending')
            ->get();
    }
    
    /**
     * Get sent friend requests from the entity.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function getSentRequests()
    {
        $friendshipModel = $this->getFriendshipModel();
        $entityIdField = $this->getEntityIdField();
        $friendIdField = $this->getFriendIdField();
        
        return $friendshipModel::where($entityIdField, $this->entityId)
            ->where('status', 'pending')
            ->get();
    }
}
