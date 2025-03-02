<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class GroupTopicsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (DB::table('group_topics')->count() === 0) {
            $users = User::all();
            $groups = DB::table('groups')->get();
            
            if ($groups->count() > 0) {
                $topicTitles = [
                    'Welcome to our group!',
                    'Introduce yourself and your pets',
                    'Tips for new pet owners',
                    'Best pet food brands',
                    'Favorite pet toys',
                    'Health concerns and solutions',
                    'Training techniques that work',
                    'Funny pet stories',
                    'Pet travel experiences',
                    'Dealing with pet loss',
                    'Adoption success stories',
                    'Breed-specific discussions',
                    'Pet photography tips',
                    'Seasonal pet care',
                    'Pet-friendly accommodations'
                ];
                
                // Create 3-5 topics for each group
                foreach ($groups as $group) {
                    $topicCount = rand(3, 5);
                    $groupMembers = DB::table('group_members')
                        ->where('group_id', $group->id)
                        ->where('status', 'active')
                        ->get();
                    
                    if ($groupMembers->count() > 0) {
                        for ($i = 0; $i < $topicCount; $i++) {
                            $member = $groupMembers->random();
                            $user = $users->firstWhere('id', $member->user_id);
                            $title = $topicTitles[array_rand($topicTitles)];
                            
                            $topicId = DB::table('group_topics')->insertGetId([
                                'title' => $title . ' ' . ($i + 1),
                                'content' => 'This is a discussion about ' . strtolower($title) . '. Feel free to share your thoughts and experiences!',
                                'group_id' => $group->id,
                                'user_id' => $user->id,
                                'is_pinned' => rand(0, 10) > 8,
                                'is_locked' => rand(0, 10) > 8,
                                'has_solution' => false,
                                'last_activity_at' => now()->subDays(rand(0, 30)),
                                'views_count' => rand(5, 100),
                                'created_at' => now()->subDays(rand(30, 60)),
                                'updated_at' => now()->subDays(rand(0, 30)),
                            ]);
                            
                            // Add creator as participant
                            DB::table('group_topic_participants')->insert([
                                'group_topic_id' => $topicId,
                                'user_id' => $user->id,
                                'created_at' => now()->subDays(rand(30, 60)),
                                'updated_at' => now()->subDays(rand(0, 30)),
                            ]);
                            
                            // Add 0-10 replies to the topic
                            $replyCount = rand(0, 10);
                            $participants = collect();
                            $participants->push($user->id);
                            
                            for ($j = 0; $j < $replyCount; $j++) {
                                $replier = $groupMembers->random();
                                $replierUser = $users->firstWhere('id', $replier->user_id);
                                
                                $replyId = DB::table('group_topic_replies')->insertGetId([
                                    'content' => 'This is reply #' . ($j + 1) . ' to the topic. Here are my thoughts on this subject...',
                                    'group_topic_id' => $topicId,
                                    'user_id' => $replierUser->id,
                                    'parent_id' => null,
                                    'is_solution' => false,
                                    'created_at' => now()->subDays(rand(0, 30)),
                                    'updated_at' => now()->subDays(rand(0, 15)),
                                ]);
                                
                                // Add replier as participant if not already
                                if (!$participants->contains($replierUser->id)) {
                                    DB::table('group_topic_participants')->insert([
                                        'group_topic_id' => $topicId,
                                        'user_id' => $replierUser->id,
                                        'created_at' => now()->subDays(rand(0, 30)),
                                        'updated_at' => now()->subDays(rand(0, 15)),
                                    ]);
                                    $participants->push($replierUser->id);
                                }
                                
                                // Add 0-3 nested replies
                                $nestedReplyCount = rand(0, 3);
                                for ($k = 0; $k < $nestedReplyCount; $k++) {
                                    $nestedReplier = $groupMembers->random();
                                    $nestedReplierUser = $users->firstWhere('id', $nestedReplier->user_id);
                                    
                                    DB::table('group_topic_replies')->insert([
                                        'content' => 'This is a nested reply to reply #' . ($j + 1) . '. I agree/disagree because...',
                                        'group_topic_id' => $topicId,
                                        'user_id' => $nestedReplierUser->id,
                                        'parent_id' => $replyId,
                                        'is_solution' => false,
                                        'created_at' => now()->subDays(rand(0, 15)),
                                        'updated_at' => now()->subDays(rand(0, 5)),
                                    ]);
                                    
                                    // Add nested replier as participant if not already
                                    if (!$participants->contains($nestedReplierUser->id)) {
                                        DB::table('group_topic_participants')->insert([
                                            'group_topic_id' => $topicId,
                                            'user_id' => $nestedReplierUser->id,
                                            'created_at' => now()->subDays(rand(0, 15)),
                                            'updated_at' => now()->subDays(rand(0, 5)),
                                        ]);
                                        $participants->push($nestedReplierUser->id);
                                    }
                                }
                            }
                            
                            // Mark a random reply as solution if there are replies
                            if ($replyCount > 0 && rand(0, 1) == 1) {
                                $replies = DB::table('group_topic_replies')
                                    ->where('group_topic_id', $topicId)
                                    ->where('parent_id', null)
                                    ->get();
                                
                                if ($replies->count() > 0) {
                                    $solutionReply = $replies->random();
                                    DB::table('group_topic_replies')
                                        ->where('id', $solutionReply->id)
                                        ->update(['is_solution' => true]);
                                    
                                    DB::table('group_topics')
                                        ->where('id', $topicId)
                                        ->update(['has_solution' => true]);
                                }
                            }
                        }
                    }
                }
                
                $this->command->info('Group topics seeded successfully.');
            } else {
                $this->command->info('No groups found. Please seed groups first.');
            }
        }
    }
}
