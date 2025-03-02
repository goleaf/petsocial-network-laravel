<?php

namespace App\Http\Livewire;

use App\Models\User;
use App\Models\Post;
use App\Models\Friendship;
use Livewire\Component;
use Livewire\WithPagination;

class FriendActivity extends Component
{
    use WithPagination;

    public $timeframe = 'week'; // 'day', 'week', 'month'
    
    protected $queryString = ['timeframe'];
    
    public function setTimeframe($timeframe)
    {
        $this->timeframe = $timeframe;
        $this->resetPage();
    }
    
    public function render()
    {
        $currentUser = auth()->user();
        $friendIds = $currentUser->friends()->pluck('id')->toArray();
        
        // Get the date range based on the selected timeframe
        $startDate = now();
        switch ($this->timeframe) {
            case 'day':
                $startDate = $startDate->subDay();
                break;
            case 'week':
                $startDate = $startDate->subWeek();
                break;
            case 'month':
                $startDate = $startDate->subMonth();
                break;
        }
        
        // Get posts from friends
        $posts = Post::whereIn('user_id', $friendIds)
            ->where('created_at', '>=', $startDate)
            ->orderBy('created_at', 'desc')
            ->paginate(10);
            
        // Get new friendships of friends
        $friendships = Friendship::where('status', 'accepted')
            ->where('created_at', '>=', $startDate)
            ->where(function ($query) use ($friendIds) {
                $query->whereIn('sender_id', $friendIds)
                    ->orWhereIn('recipient_id', $friendIds);
            })
            ->orderBy('created_at', 'desc')
            ->get();
            
        // Combine and sort activities
        $activities = [];
        
        foreach ($posts as $post) {
            $activities[] = [
                'type' => 'post',
                'data' => $post,
                'created_at' => $post->created_at,
                'user' => $post->user
            ];
        }
        
        foreach ($friendships as $friendship) {
            // Only include if both users are friends with the current user
            if (in_array($friendship->sender_id, $friendIds) && in_array($friendship->recipient_id, $friendIds)) {
                $activities[] = [
                    'type' => 'friendship',
                    'data' => $friendship,
                    'created_at' => $friendship->created_at,
                    'sender' => User::find($friendship->sender_id),
                    'recipient' => User::find($friendship->recipient_id)
                ];
            }
        }
        
        // Sort activities by created_at (newest first)
        usort($activities, function($a, $b) {
            return $b['created_at']->timestamp <=> $a['created_at']->timestamp;
        });
        
        return view('livewire.friend-activity', [
            'activities' => $activities,
            'postsCount' => $posts->total(),
            'friendshipsCount' => $friendships->count()
        ])->layout('layouts.app');
    }
}
