<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Post;
use App\Models\Comment;

class CommentsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (DB::table('comments')->count() === 0) {
            $users = User::all();
            $posts = Post::all();
            
            foreach ($posts as $post) {
                // Each post has 0-5 comments
                $commentCount = rand(0, 5);
                
                for ($i = 0; $i < $commentCount; $i++) {
                    $user = $users->random();
                    $comment = Comment::create([
                        'user_id' => $user->id,
                        'post_id' => $post->id,
                        'parent_id' => null,
                        'content' => "This is a comment by {$user->name} on post {$post->id}.",
                        'created_at' => now()->subDays(rand(0, 30))->subHours(rand(0, 24)),
                        'updated_at' => now()->subDays(rand(0, 30))->subHours(rand(0, 24)),
                        'deleted_at' => rand(0, 10) === 0 ? now() : null, // 10% chance of being soft deleted
                    ]);
                    
                    // 30% chance of having a reply
                    if (rand(0, 100) < 30) {
                        $replyUser = $users->random();
                        Comment::create([
                            'user_id' => $replyUser->id,
                            'post_id' => $post->id,
                            'parent_id' => $comment->id,
                            'content' => "This is a reply by {$replyUser->name} to {$user->name}'s comment.",
                            'created_at' => now()->subDays(rand(0, 30))->subHours(rand(0, 24)),
                            'updated_at' => now()->subDays(rand(0, 30))->subHours(rand(0, 24)),
                            'deleted_at' => null,
                        ]);
                    }
                }
            }
            
            $this->command->info('Comments seeded successfully.');
        }
    }
}
