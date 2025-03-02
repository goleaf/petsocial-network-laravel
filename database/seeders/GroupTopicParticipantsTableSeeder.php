<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\User;

class GroupTopicParticipantsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (DB::table('group_topic_participants')->count() === 0) {
            // Check if group_topics table exists and has records
            $hasGroupTopics = Schema::hasTable('group_topics') && DB::table('group_topics')->count() > 0;
            
            if (!$hasGroupTopics) {
                $this->command->info('Cannot seed group topic participants: group_topics table is empty or does not exist. Please run GroupTopicsTableSeeder first.');
                return;
            }
            
            $users = User::all();
            $groupTopics = DB::table('group_topics')->get();
            
            // For each group topic, add some participants
            foreach ($groupTopics as $topic) {
                // Get group members for this topic's group
                $groupMembers = DB::table('group_members')
                    ->where('group_id', $topic->group_id)
                    ->get();
                
                if ($groupMembers->count() == 0) {
                    continue;
                }
                
                // Add 1-5 participants per topic
                $participantCount = rand(1, min(5, $groupMembers->count()));
                $memberIndices = array_rand($groupMembers->toArray(), $participantCount);
                
                if (!is_array($memberIndices)) {
                    $memberIndices = [$memberIndices];
                }
                
                foreach ($memberIndices as $index) {
                    $member = $groupMembers[$index];
                    $createdAt = now()->subDays(rand(0, 30));
                    
                    // Check if this participant already exists
                    $exists = DB::table('group_topic_participants')
                        ->where('group_topic_id', $topic->id)
                        ->where('user_id', $member->user_id)
                        ->exists();
                    
                    if (!$exists) {
                        DB::table('group_topic_participants')->insert([
                            'group_topic_id' => $topic->id,
                            'user_id' => $member->user_id,
                            'last_read_at' => rand(0, 1) ? $createdAt : null,
                            'created_at' => $createdAt,
                            'updated_at' => $createdAt,
                        ]);
                    }
                }
            }
            
            $this->command->info('Group topic participants seeded successfully.');
        } else {
            $this->command->info('Group topic participants already seeded.');
        }
    }
}
