<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Pet;

class ActivityLogsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (DB::table('activity_logs')->count() === 0) {
            $users = User::all();
            $pets = Pet::all();
            
            $activityTypes = [
                'login', 'logout', 'post_created', 'post_edited', 'post_deleted',
                'comment_created', 'comment_edited', 'comment_deleted',
                'friend_request_sent', 'friend_request_accepted', 'friend_request_declined',
                'profile_updated', 'pet_added', 'pet_updated', 'pet_removed',
                'group_joined', 'group_left', 'group_created', 'group_updated', 'group_deleted'
            ];
            
            // Create user activities
            foreach ($users as $user) {
                $activityCount = rand(3, 10);
                
                for ($i = 0; $i < $activityCount; $i++) {
                    $activityType = $activityTypes[array_rand($activityTypes)];
                    $createdAt = now()->subDays(rand(0, 30))->subHours(rand(0, 24));
                    
                    DB::table('activity_logs')->insert([
                        'user_id' => $user->id,
                        'action' => $activityType,
                        'description' => "User {$user->name} performed action: {$activityType}",
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                    ]);
                }
            }
            
            $this->command->info('Activity logs seeded successfully.');
        }
    }
}
