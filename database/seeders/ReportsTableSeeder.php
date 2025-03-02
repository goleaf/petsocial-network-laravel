<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Post;
use App\Models\Comment;
use App\Models\Pet;

class ReportsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (DB::table('reports')->count() === 0) {
            $users = User::all();
            $posts = Post::all();
            $comments = Comment::all();
            $pets = Pet::all();
            
            if ($users->count() == 0) {
                $this->command->info('No users found. Please seed users first.');
                return;
            }
            
            $reportableTypes = [
                'App\\Models\\User' => $users,
                'App\\Models\\Post' => $posts,
                'App\\Models\\Comment' => $comments,
                'App\\Models\\Pet' => $pets,
            ];
            
            $reasons = [
                'spam' => 'This content contains spam',
                'harassment' => 'This content contains harassment or bullying',
                'inappropriate' => 'This content is inappropriate',
                'hate_speech' => 'This content contains hate speech',
                'violence' => 'This content promotes violence',
                'other' => 'Other reason'
            ];
            
            $statuses = ['pending', 'resolved', 'dismissed'];
            
            // Create reports for different types of content
            $reportCount = 20;
            
            for ($i = 0; $i < $reportCount; $i++) {
                $reporter = $users->random();
                
                // Select a random reportable type
                $reportableType = array_rand($reportableTypes);
                $reportables = $reportableTypes[$reportableType];
                
                if ($reportables->count() == 0) {
                    continue;
                }
                
                $reportable = $reportables->random();
                $reasonKey = array_rand($reasons);
                $status = $statuses[array_rand($statuses)];
                $createdAt = now()->subDays(rand(0, 30));
                
                // Ensure this report doesn't already exist
                $existingReport = DB::table('reports')
                    ->where('user_id', $reporter->id)
                    ->where('reportable_type', $reportableType)
                    ->where('reportable_id', $reportable->id)
                    ->first();
                
                if (!$existingReport) {
                    $resolvedBy = $status !== 'pending' ? $users->except($reporter->id)->random()->id : null;
                    $resolvedAt = $status !== 'pending' ? $createdAt->addDays(rand(1, 5)) : null;
                    
                    DB::table('reports')->insert([
                        'user_id' => $reporter->id,
                        'reportable_type' => $reportableType,
                        'reportable_id' => $reportable->id,
                        'reason' => $reasons[$reasonKey],
                        'status' => $status,
                        'notes' => 'Report notes: ' . $reasonKey,
                        'resolved_by' => $resolvedBy,
                        'resolved_at' => $resolvedAt,
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                    ]);
                }
            }
            
            $this->command->info('Reports seeded successfully.');
        }
    }
}
