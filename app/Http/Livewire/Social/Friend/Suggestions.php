<?php

namespace App\Http\Livewire\Social\Friend;

use App\Models\User;
use Livewire\Component;

class Suggestions extends Component
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
                $mutualFriends = User::whereIn('id', $mutualFriendIds)->get();
                // Add score based on mutual friends (more weight)
                $score += $mutualFriendCount * 2;
            }
            
            // Calculate mutual interests
            $userInterests = optional($user->profile)->interests ?? [];
            $mutualInterests = array_intersect($myInterests, $userInterests);
            $mutualInterestCount = count($mutualInterests);
            
            if ($mutualInterestCount > 0) {
                // Add score based on mutual interests
                $score += $mutualInterestCount;
            }
            
            // Only add to suggestions if there's at least one mutual connection or interest
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
        return view('livewire.friends.suggestions');
    }
}
