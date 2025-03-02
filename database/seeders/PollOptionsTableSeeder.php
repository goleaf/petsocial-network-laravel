<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Poll;

class PollOptionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (DB::table('poll_options')->count() === 0) {
            $polls = DB::table('polls')->get();
            
            if ($polls->count() == 0) {
                $this->command->info('No polls found. Please seed polls first.');
                return;
            }
            
            $optionTexts = [
                'Yes', 'No', 'Maybe', 
                'Strongly Agree', 'Agree', 'Neutral', 'Disagree', 'Strongly Disagree',
                'Morning', 'Afternoon', 'Evening', 'Night',
                'Dogs', 'Cats', 'Birds', 'Fish', 'Reptiles', 'Small mammals',
                'Red', 'Blue', 'Green', 'Yellow', 'Purple', 'Orange', 'Black', 'White',
                'Daily', 'Weekly', 'Monthly', 'Rarely', 'Never'
            ];
            
            // For each poll, add 2-5 options
            foreach ($polls as $poll) {
                $optionCount = rand(2, 5);
                $selectedOptions = array_rand($optionTexts, $optionCount);
                
                if (!is_array($selectedOptions)) {
                    $selectedOptions = [$selectedOptions];
                }
                
                foreach ($selectedOptions as $index) {
                    $createdAt = now()->subDays(rand(0, 30));
                    
                    DB::table('poll_options')->insert([
                        'poll_id' => $poll->id,
                        'text' => $optionTexts[$index],
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                    ]);
                }
            }
            
            $this->command->info('Poll options seeded successfully.');
        }
    }
}
