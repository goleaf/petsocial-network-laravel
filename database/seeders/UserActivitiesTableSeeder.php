<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Post;
use App\Models\Comment;

class UserActivitiesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (DB::table('user_activities')->count() === 0) {
            $users = User::all();
            $posts = Post::all();
            $comments = Comment::all();
            
            if ($users->count() == 0) {
                $this->command->info('No users found. Please seed users first.');
                return;
            }
            
            $activityTypes = [
                'post_created', 'post_edited', 'post_deleted',
                'comment_created', 'comment_edited', 'comment_deleted',
                'friend_request_sent', 'friend_request_accepted',
                'profile_updated', 'pet_added', 'pet_updated',
                'group_joined', 'group_left', 'group_created',
                'reaction_added', 'reaction_removed'
            ];
            
            // Create activities for each user
            foreach ($users as $user) {
                $activityCount = rand(5, 15);
                
                for ($i = 0; $i < $activityCount; $i++) {
                    $activityType = $activityTypes[array_rand($activityTypes)];
                    $createdAt = now()->subDays(rand(0, 30))->subHours(rand(0, 24));
                    
                    // Determine the target based on activity type
                    $targetType = null;
                    $targetId = null;
                    
                    if (strpos($activityType, 'post') === 0 && $posts->count() > 0) {
                        $targetType = 'App\\Models\\Post';
                        $targetId = $posts->random()->id;
                    } elseif (strpos($activityType, 'comment') === 0 && $comments->count() > 0) {
                        $targetType = 'App\\Models\\Comment';
                        $targetId = $comments->random()->id;
                    } elseif (strpos($activityType, 'friend') === 0 && $users->count() > 1) {
                        $targetType = 'App\\Models\\User';
                        $targetId = $users->except($user->id)->random()->id;
                    }
                    
                    DB::table('user_activities')->insert([
                        'user_id' => $user->id,
                        'type' => $activityType,
                        'description' => 'User performed action: ' . $activityType,
                        'data' => json_encode([
                            'ip_address' => '127.0.0.1',
                            'user_agent' => 'Seeder/1.0',
                            'device' => 'Seeder Device'
                        ]),
                        'actor_type' => 'App\\Models\\User',
                        'actor_id' => $user->id,
                        'target_type' => $targetType,
                        'target_id' => $targetId,
                        'read' => rand(0, 1),
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                    ]);
                }
            }
            
            $this->command->info('User activities seeded successfully.');
        }
    }
}
