<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Post;
use App\Models\Tag;

class PostTagTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (DB::table('post_tag')->count() === 0) {
            $posts = Post::all();
            $tags = Tag::all();
            
            if ($posts->count() == 0 || $tags->count() == 0) {
                $this->command->info('No posts or tags found. Please seed posts and tags first.');
                return;
            }
            
            $tagsArray = $tags->toArray();
            
            // Assign tags to posts
            foreach ($posts as $post) {
                // Each post gets 0-5 tags
                $tagCount = rand(0, min(5, count($tagsArray)));
                
                if ($tagCount > 0) {
                    // Get random tag indices
                    $tagIndices = array_rand($tagsArray, $tagCount);
                    
                    // Make sure $tagIndices is an array even if only one tag is selected
                    if (!is_array($tagIndices)) {
                        $tagIndices = [$tagIndices];
                    }
                    
                    foreach ($tagIndices as $index) {
                        $tagId = $tagsArray[$index]['id'];
                        
                        // Check if this post-tag relationship already exists
                        $exists = DB::table('post_tag')
                            ->where('post_id', $post->id)
                            ->where('tag_id', $tagId)
                            ->exists();
                        
                        if (!$exists) {
                            DB::table('post_tag')->insert([
                                'post_id' => $post->id,
                                'tag_id' => $tagId,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }
                    }
                }
            }
            
            $this->command->info('Post tags seeded successfully.');
        } else {
            $this->command->info('Post tags already seeded.');
        }
    }
}
