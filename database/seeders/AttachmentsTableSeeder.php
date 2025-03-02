<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Post;
use App\Models\User;
use App\Models\Comment;

class AttachmentsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (DB::table('attachments')->count() === 0) {
            $posts = Post::all();
            $comments = Comment::all();
            $users = User::all();
            $fileTypes = ['image/jpeg', 'video/mp4', 'application/pdf', 'text/plain'];
            
            // Post attachments
            foreach ($posts as $post) {
                if (rand(0, 1)) {
                    $fileType = $fileTypes[rand(0, count($fileTypes) - 1)];
                    $extension = $fileType === 'image/jpeg' ? 'jpg' : 
                                ($fileType === 'video/mp4' ? 'mp4' : 
                                ($fileType === 'application/pdf' ? 'pdf' : 'txt'));
                    
                    $attachmentData = [];
                    
                    // Check for different column names in the schema
                    if (Schema::hasColumn('attachments', 'file_path')) {
                        $attachmentData = [
                            'attachable_type' => 'App\\Models\\Post',
                            'attachable_id' => $post->id,
                            'file_path' => "uploads/attachments/post_{$post->id}_attachment.$extension",
                            'file_name' => "attachment_{$post->id}.$extension",
                            'file_size' => rand(10000, 5000000),
                            'file_type' => $fileType,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    } else {
                        $attachmentData = [
                            'attachable_type' => 'App\\Models\\Post',
                            'attachable_id' => $post->id,
                            'path' => "uploads/attachments/post_{$post->id}_attachment.$extension",
                            'original_name' => "attachment_{$post->id}.$extension",
                            'size' => rand(10000, 5000000),
                            'mime_type' => $fileType,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                        
                        // Add type if the column exists
                        if (Schema::hasColumn('attachments', 'type')) {
                            $attachmentData['type'] = $fileType === 'image/jpeg' ? 'image' : 
                                                    ($fileType === 'video/mp4' ? 'video' : 
                                                    ($fileType === 'application/pdf' ? 'document' : 'text'));
                        }
                    }
                    
                    // Add user_id if the column exists
                    if (Schema::hasColumn('attachments', 'user_id')) {
                        $attachmentData['user_id'] = $post->user_id ?? $users->random()->id;
                    }
                    
                    // Add description if the column exists
                    if (Schema::hasColumn('attachments', 'description')) {
                        $attachmentData['description'] = rand(0, 1) ? "Description for attachment on post {$post->id}" : null;
                    }
                    
                    DB::table('attachments')->insert($attachmentData);
                }
            }
            
            // Comment attachments (if applicable)
            if (rand(0, 1)) {
                foreach ($comments->random(min(5, $comments->count())) as $comment) {
                    $fileType = $fileTypes[rand(0, count($fileTypes) - 1)];
                    $extension = $fileType === 'image/jpeg' ? 'jpg' : 
                                ($fileType === 'video/mp4' ? 'mp4' : 
                                ($fileType === 'application/pdf' ? 'pdf' : 'txt'));
                    
                    $attachmentData = [];
                    
                    // Check for different column names in the schema
                    if (Schema::hasColumn('attachments', 'file_path')) {
                        $attachmentData = [
                            'attachable_type' => 'App\\Models\\Comment',
                            'attachable_id' => $comment->id,
                            'file_path' => "uploads/attachments/comment_{$comment->id}_attachment.$extension",
                            'file_name' => "attachment_comment_{$comment->id}.$extension",
                            'file_size' => rand(10000, 5000000),
                            'file_type' => $fileType,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    } else {
                        $attachmentData = [
                            'attachable_type' => 'App\\Models\\Comment',
                            'attachable_id' => $comment->id,
                            'path' => "uploads/attachments/comment_{$comment->id}_attachment.$extension",
                            'original_name' => "attachment_comment_{$comment->id}.$extension",
                            'size' => rand(10000, 5000000),
                            'mime_type' => $fileType,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                        
                        // Add type if the column exists
                        if (Schema::hasColumn('attachments', 'type')) {
                            $attachmentData['type'] = $fileType === 'image/jpeg' ? 'image' : 
                                                    ($fileType === 'video/mp4' ? 'video' : 
                                                    ($fileType === 'application/pdf' ? 'document' : 'text'));
                        }
                    }
                    
                    // Add user_id if the column exists
                    if (Schema::hasColumn('attachments', 'user_id')) {
                        $attachmentData['user_id'] = $comment->user_id ?? $users->random()->id;
                    }
                    
                    // Add description if the column exists
                    if (Schema::hasColumn('attachments', 'description')) {
                        $attachmentData['description'] = rand(0, 1) ? "Description for attachment on comment {$comment->id}" : null;
                    }
                    
                    DB::table('attachments')->insert($attachmentData);
                }
            }
            
            $this->command->info('Attachments seeded successfully.');
        }
    }
}
