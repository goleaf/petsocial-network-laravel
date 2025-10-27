<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use App\Models\Post;

class PollsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (DB::table('polls')->count() === 0) {
            // Check if group_topics table exists and has records
            $hasGroupTopics = Schema::hasTable('group_topics') && DB::table('group_topics')->count() > 0;
            
            if (!$hasGroupTopics) {
                $this->command->info('Cannot seed polls: group_topics table is empty or does not exist. Please run GroupTopicsTableSeeder first.');
                return;
            }
            
            $users = User::all();
            $posts = Post::all();
            $groupTopics = DB::table('group_topics')->get();
            
            if ($groupTopics->count() == 0) {
                $this->command->info('No group topics found. Please seed group topics first.');
                return;
            }
            
            // Create polls
            for ($i = 0; $i < 10; $i++) {
                $user = $users->random();
                $groupTopic = $groupTopics->random();
                
                $pollData = [
                    'group_topic_id' => $groupTopic->id,
                    'user_id' => $user->id,
                    'question' => 'What is your favorite pet ' . ($i + 1) . '?',
                    'allow_multiple' => rand(0, 1) ? true : false,
                    'is_anonymous' => rand(0, 1) ? true : false,
                    'expires_at' => now()->addDays(rand(1, 30)),
                    'created_at' => now()->subDays(rand(0, 15)),
                    'updated_at' => now(),
                ];
                
                $pollId = DB::table('polls')->insertGetId($pollData);
                
                // Create poll options
                $options = ['Dog', 'Cat', 'Bird', 'Fish', 'Rabbit', 'Hamster', 'Turtle', 'Other'];
                $selectedOptions = array_slice($options, 0, rand(2, count($options)));
                
                foreach ($selectedOptions as $index => $option) {
                    DB::table('poll_options')->insert([
                        'poll_id' => $pollId,
                        'text' => $option,
                        // Persist the order so seeded options mirror builder behaviour.
                        'display_order' => $index,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
                
                // Create poll votes
                $pollOptions = DB::table('poll_options')->where('poll_id', $pollId)->get();
                $votingUsers = $users->random(rand(0, min(20, $users->count())));
                
                foreach ($votingUsers as $votingUser) {
                    $selectedOption = $pollOptions->random();

                    DB::table('poll_votes')->insert([
                        'poll_id' => $pollId,
                        'poll_option_id' => $selectedOption->id,
                        'user_id' => $votingUser->id,
                        'created_at' => now()->subDays(rand(0, 10)),
                        'updated_at' => now(),
                    ]);
                }
            }
            
            $this->command->info('Polls seeded successfully.');
        }
    }
}
