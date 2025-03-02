<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PollVotesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (DB::table('poll_votes')->count() === 0) {
            $users = DB::table('users')->get();
            $pollOptions = DB::table('poll_options')->get();
            
            if ($pollOptions->count() == 0) {
                $this->command->info('No poll options found. Please seed poll options first.');
                return;
            }
            
            if ($users->count() == 0) {
                $this->command->info('No users found. Please seed users first.');
                return;
            }
            
            // Group poll options by poll_id
            $optionsByPoll = [];
            foreach ($pollOptions as $option) {
                if (!isset($optionsByPoll[$option->poll_id])) {
                    $optionsByPoll[$option->poll_id] = [];
                }
                $optionsByPoll[$option->poll_id][] = $option;
            }
            
            // For each poll, add some random votes
            foreach ($optionsByPoll as $pollId => $options) {
                // Get random number of voters (between 5 and 20)
                $voterCount = rand(5, min(20, $users->count()));
                $voterIndices = array_rand($users->toArray(), $voterCount);
                
                if (!is_array($voterIndices)) {
                    $voterIndices = [$voterIndices];
                }
                
                foreach ($voterIndices as $index) {
                    $voter = $users[$index];
                    
                    // Select a random option for this poll
                    $option = $options[array_rand($options)];
                    $createdAt = now()->subDays(rand(0, 30));
                    
                    // Check if this vote already exists
                    $exists = DB::table('poll_votes')
                        ->where('poll_option_id', $option->id)
                        ->where('user_id', $voter->id)
                        ->exists();
                    
                    if (!$exists) {
                        DB::table('poll_votes')->insert([
                            'poll_option_id' => $option->id,
                            'user_id' => $voter->id,
                            'created_at' => $createdAt,
                            'updated_at' => $createdAt,
                        ]);
                    }
                }
            }
            
            $this->command->info('Poll votes seeded successfully.');
        }
    }
}
