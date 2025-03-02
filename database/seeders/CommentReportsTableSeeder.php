<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Comment;

class CommentReportsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (DB::table('comment_reports')->count() === 0) {
            $users = User::all();
            $comments = Comment::all();
            
            if ($comments->count() == 0) {
                $this->command->info('No comments found. Please seed comments first.');
                return;
            }
            
            // Create reports for some comments
            $reportCount = min(15, $comments->count() * 0.1); // Report about 10% of comments
            
            for ($i = 0; $i < $reportCount; $i++) {
                $reporter = $users->random();
                $comment = $comments->random();
                
                // Ensure this report doesn't already exist
                $existingReport = DB::table('comment_reports')
                    ->where('user_id', $reporter->id)
                    ->where('comment_id', $comment->id)
                    ->first();
                
                if (!$existingReport) {
                    $createdAt = now()->subDays(rand(0, 30));
                    $reasons = ['Spam', 'Harassment', 'Inappropriate content', 'Hate speech', 'Violence', 'Other'];
                    
                    DB::table('comment_reports')->insert([
                        'user_id' => $reporter->id,
                        'comment_id' => $comment->id,
                        'reason' => $reasons[rand(0, count($reasons) - 1)],
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                    ]);
                }
            }
            
            $this->command->info('Comment reports seeded successfully.');
        }
    }
}
