<?php

namespace App\Http\Livewire;

use App\Models\User;
use Livewire\Component;

class FriendSuggestions extends Component
{
    public $suggestions;

    public function mount()
    {
        $this->loadSuggestions();
    }

    public function loadSuggestions()
    {
        $currentUser = auth()->user();
        $friendIds = $currentUser->friends()->pluck('users.id')->push($currentUser->id);
        // Explicitly pluck 'users.id' from the blocks relationship
        $blockedIds = optional($currentUser->blocks())->pluck('users.id') ?? collect();
        
        // Get users who aren't friends or blocked
        $potentialFriends = User::whereNotIn('id', $friendIds)
            ->whereNotIn('id', $blockedIds)
            ->get();
            
        // Calculate mutual friends and interests for each potential friend
        $suggestionsWithScore = [];
        $myFriendIds = $currentUser->friends()->pluck('id')->toArray();
        $myInterests = optional($currentUser->profile)->interests ?? [];
        
        foreach ($potentialFriends as $user) {
            $score = 0;
            $mutualFriends = [];
            $mutualInterestCount = 0;
            
            // Calculate mutual friends
            $userFriendIds = $user->friends()->pluck('id')->toArray();
            $mutualFriendIds = array_intersect($myFriendIds, $userFriendIds);
            $mutualFriendCount = count($mutualFriendIds);
            
            if ($mutualFriendCount > 0) {
                $score += $mutualFriendCount * 2; // Weight mutual friends higher
                $mutualFriends = User::whereIn('id', $mutualFriendIds)->limit(3)->get();
            }
            
            // Calculate mutual interests
            $userInterests = optional($user->profile)->interests ?? [];
            if (!empty($myInterests) && !empty($userInterests)) {
                $mutualInterestCount = count(array_intersect($myInterests, $userInterests));
                $score += $mutualInterestCount;
            }
            
            // Only include users with some connection
            if ($score > 0) {
                $suggestionsWithScore[] = [
                    'user' => $user,
                    'score' => $score,
                    'mutual_friends' => $mutualFriends,
                    'mutual_friend_count' => $mutualFriendCount,
                    'mutual_interest_count' => $mutualInterestCount
                ];
            }
        }
        
        // Sort by score (descending)
        usort($suggestionsWithScore, function($a, $b) {
            return $b['score'] <=> $a['score'];
        });
        
        // Take top 5 suggestions
        $this->suggestions = collect(array_slice($suggestionsWithScore, 0, 5));
    }

    public function render()
    {
        return view('livewire.friend-suggestions');
    }
}
