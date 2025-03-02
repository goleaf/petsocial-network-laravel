<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Post;

class SharesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (DB::table('shares')->count() === 0) {
            $users = User::all();
            $posts = Post::all();
            
            if ($posts->count() == 0) {
                $this->command->info('No posts found. Please seed posts first.');
                return;
            }
            
            // Create shares for some posts
            $shareCount = min(30, $posts->count() * 0.2); // Share about 20% of posts
            
            for ($i = 0; $i < $shareCount; $i++) {
                $user = $users->random();
                $post = $posts->random();
                
                // Ensure this share doesn't already exist
                $existingShare = DB::table('shares')
                    ->where('user_id', $user->id)
                    ->where('post_id', $post->id)
                    ->first();
                
                if (!$existingShare) {
                    $createdAt = now()->subDays(rand(0, 30));
                    
                    DB::table('shares')->insert([
                        'user_id' => $user->id,
                        'post_id' => $post->id,
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                    ]);
                }
            }
            
            $this->command->info('Shares seeded successfully.');
        }
    }
}
