<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Post;

class PostReportsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (DB::table('post_reports')->count() === 0) {
            $users = User::all();
            $posts = Post::all();
            
            if ($posts->count() == 0) {
                $this->command->info('No posts found. Please seed posts first.');
                return;
            }
            
            // Create reports for some posts
            $reportCount = min(15, $posts->count() * 0.1); // Report about 10% of posts
            
            for ($i = 0; $i < $reportCount; $i++) {
                $reporter = $users->random();
                $post = $posts->random();
                
                // Ensure this report doesn't already exist
                $existingReport = DB::table('post_reports')
                    ->where('user_id', $reporter->id)
                    ->where('post_id', $post->id)
                    ->first();
                
                if (!$existingReport) {
                    $createdAt = now()->subDays(rand(0, 30));
                    $reasons = ['Spam', 'Harassment', 'Inappropriate content', 'Hate speech', 'Violence', 'Other'];
                    
                    DB::table('post_reports')->insert([
                        'user_id' => $reporter->id,
                        'post_id' => $post->id,
                        'reason' => $reasons[rand(0, count($reasons) - 1)],
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                    ]);
                }
            }
            
            $this->command->info('Post reports seeded successfully.');
        }
    }
}
