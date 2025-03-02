<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\User;

class GroupTopicRepliesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (DB::table('group_topic_replies')->count() === 0) {
            // Check if group_topics table exists and has records
            $hasGroupTopics = Schema::hasTable('group_topics') && DB::table('group_topics')->count() > 0;
            
            if (!$hasGroupTopics) {
                $this->command->info('Cannot seed group topic replies: group_topics table is empty or does not exist. Please run GroupTopicsTableSeeder first.');
                return;
            }
            
            $users = User::all();
            $groupTopics = DB::table('group_topics')->get();
            
            // For each group topic, add some replies
            foreach ($groupTopics as $topic) {
                // Get group members for this topic's group
                $groupMembers = DB::table('group_members')
                    ->where('group_id', $topic->group_id)
                    ->get();
                
                if ($groupMembers->count() == 0) {
                    continue;
                }
                
                // Add 0-10 replies per topic
                $replyCount = rand(0, 10);
                $groupMembersArray = $groupMembers->toArray();
                
                for ($i = 0; $i < $replyCount; $i++) {
                    $memberIndex = array_rand($groupMembersArray);
                    $member = $groupMembers[$memberIndex];
                    $createdAt = now()->subDays(rand(0, 30));
                    
                    $replyId = DB::table('group_topic_replies')->insertGetId([
                        'group_topic_id' => $topic->id,
                        'user_id' => $member->user_id,
                        'content' => "This is a reply to the topic: " . $topic->title . ". Reply #" . ($i + 1),
                        'parent_id' => null,
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                    ]);
                    
                    // Add 0-3 nested replies
                    $nestedReplyCount = rand(0, 3);
                    
                    for ($j = 0; $j < $nestedReplyCount; $j++) {
                        $nestedMemberIndex = array_rand($groupMembersArray);
                        $nestedMember = $groupMembers[$nestedMemberIndex];
                        $nestedCreatedAt = clone $createdAt;
                        $nestedCreatedAt = $nestedCreatedAt->addHours(rand(1, 24));
                        
                        DB::table('group_topic_replies')->insert([
                            'group_topic_id' => $topic->id,
                            'user_id' => $nestedMember->user_id,
                            'content' => "This is a nested reply to reply #" . ($i + 1),
                            'parent_id' => $replyId,
                            'created_at' => $nestedCreatedAt,
                            'updated_at' => $nestedCreatedAt,
                        ]);
                    }
                }
            }
            
            $this->command->info('Group topic replies seeded successfully.');
        } else {
            $this->command->info('Group topic replies already seeded.');
        }
    }
}
