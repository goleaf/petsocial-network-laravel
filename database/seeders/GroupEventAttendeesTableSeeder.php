<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\GroupEvent;

class GroupEventAttendeesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (DB::table('group_event_attendees')->count() === 0) {
            $users = User::all();
            $events = GroupEvent::all();
            
            if ($events->count() == 0) {
                $this->command->info('No group events found. Please seed group events first.');
                return;
            }
            
            if ($users->count() == 0) {
                $this->command->info('No users found. Please seed users first.');
                return;
            }
            
            $usersArray = $users->toArray();
            
            // For each event, add some random attendees
            foreach ($events as $event) {
                // Get random number of attendees (between 1 and 10)
                $attendeeCount = rand(1, min(10, count($usersArray)));
                
                // Get random user indices
                $userIndices = array_rand($usersArray, $attendeeCount);
                
                // Make sure $userIndices is an array even if only one user is selected
                if (!is_array($userIndices)) {
                    $userIndices = [$userIndices];
                }
                
                foreach ($userIndices as $index) {
                    $userId = $usersArray[$index]['id'];
                    
                    // Check if this attendance record already exists
                    $exists = DB::table('group_event_attendees')
                        ->where('group_event_id', $event->id)
                        ->where('user_id', $userId)
                        ->exists();
                    
                    if (!$exists) {
                        // Random attendance status: going, maybe, not_going
                        $statuses = ['going', 'maybe', 'not_going'];
                        $status = $statuses[array_rand($statuses)];
                        
                        $createdAt = now()->subDays(rand(0, 30));
                        
                        DB::table('group_event_attendees')->insert([
                            'group_event_id' => $event->id,
                            'user_id' => $userId,
                            'status' => 'going', // Only use 'going' status as it's the only valid status in the constraint
                            'reminder_set' => rand(0, 1),
                            'created_at' => $createdAt,
                            'updated_at' => $createdAt,
                        ]);
                    }
                }
            }
            
            $this->command->info('Group event attendees seeded successfully.');
        }
    }
}
