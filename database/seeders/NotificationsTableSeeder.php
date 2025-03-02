<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use App\Models\Post;
use App\Models\Comment;

class NotificationsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (DB::table('notifications')->count() === 0) {
            $users = User::all();
            $posts = Post::all();
            $comments = Comment::all();
            
            $notificationTypes = [
                'new_comment', 'new_reaction', 'new_friend_request', 
                'friend_request_accepted', 'post_mention', 'comment_mention'
            ];
            
            foreach ($users as $user) {
                // Each user has 0-10 notifications
                for ($i = 0; $i < rand(0, 10); $i++) {
                    $notificationType = $notificationTypes[rand(0, count($notificationTypes) - 1)];
                    $data = [];
                    $notifiableType = 'App\\Models\\User';
                    $notifiableId = $user->id;
                    
                    // Create notification data based on type
                    switch ($notificationType) {
                        case 'new_comment':
                            if ($posts->count() > 0) {
                                $post = $posts->random();
                                $commenter = $users->where('id', '!=', $user->id)->random();
                                $data = [
                                    'post_id' => $post->id,
                                    'commenter_id' => $commenter->id,
                                    'commenter_name' => $commenter->name
                                ];
                            }
                            break;
                            
                        case 'new_reaction':
                            if ($posts->count() > 0) {
                                $post = $posts->random();
                                $reactor = $users->where('id', '!=', $user->id)->random();
                                $data = [
                                    'post_id' => $post->id,
                                    'reactor_id' => $reactor->id,
                                    'reactor_name' => $reactor->name,
                                    'reaction_type' => ['like', 'love', 'haha', 'wow', 'sad', 'angry'][rand(0, 5)]
                                ];
                            }
                            break;
                            
                        case 'new_friend_request':
                            $requester = $users->where('id', '!=', $user->id)->random();
                            $data = [
                                'requester_id' => $requester->id,
                                'requester_name' => $requester->name
                            ];
                            break;
                            
                        case 'friend_request_accepted':
                            $accepter = $users->where('id', '!=', $user->id)->random();
                            $data = [
                                'accepter_id' => $accepter->id,
                                'accepter_name' => $accepter->name
                            ];
                            break;
                            
                        case 'post_mention':
                            if ($posts->count() > 0) {
                                $post = $posts->random();
                                $mentioner = $users->where('id', '!=', $user->id)->random();
                                $data = [
                                    'post_id' => $post->id,
                                    'mentioner_id' => $mentioner->id,
                                    'mentioner_name' => $mentioner->name
                                ];
                            }
                            break;
                            
                        case 'comment_mention':
                            if ($comments->count() > 0) {
                                $comment = $comments->random();
                                $mentioner = $users->where('id', '!=', $user->id)->random();
                                $data = [
                                    'comment_id' => $comment->id,
                                    'post_id' => $comment->post_id,
                                    'mentioner_id' => $mentioner->id,
                                    'mentioner_name' => $mentioner->name
                                ];
                            }
                            break;
                    }
                    
                    // Only insert if we have valid data
                    if (!empty($data)) {
                        $notificationData = [
                            'user_id' => $user->id,
                            'type' => $notificationType,
                            'notifiable_type' => $notifiableType,
                            'notifiable_id' => $notifiableId,
                            'data' => json_encode($data),
                            'read_at' => rand(0, 1) ? now()->subDays(rand(0, 5)) : null,
                            'created_at' => now()->subDays(rand(0, 30))->subHours(rand(0, 24)),
                            'updated_at' => now()->subDays(rand(0, 15))->subHours(rand(0, 24)),
                        ];
                        
                        DB::table('notifications')->insert($notificationData);
                    }
                }
            }
            
            $this->command->info('Notifications seeded successfully.');
        }
    }
}
