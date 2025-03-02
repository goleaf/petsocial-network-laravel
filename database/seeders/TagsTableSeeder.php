<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Post;
use App\Models\Tag;

class TagsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (DB::table('tags')->count() === 0) {
            // Create common tags
            $tags = [
                'pets', 'dogs', 'cats', 'birds', 'rabbits', 'hamsters', 'turtles', 'fish',
                'cute', 'adorable', 'funny', 'playful', 'sleepy', 'hungry', 'happy',
                'training', 'grooming', 'health', 'nutrition', 'toys', 'accessories',
                'adoption', 'rescue', 'shelter', 'petcare', 'veterinary', 'petsitting',
                'dogwalking', 'petfriendly', 'animallovers'
            ];
            
            foreach ($tags as $tagName) {
                Tag::create([
                    'name' => $tagName,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            
            // Attach tags to posts
            $posts = Post::all();
            $allTags = Tag::all();
            
            foreach ($posts as $post) {
                // Each post gets 0-5 tags
                $tagCount = rand(0, 5);
                $postTags = $allTags->random(min($tagCount, $allTags->count()));
                
                foreach ($postTags as $tag) {
                    DB::table('post_tag')->insert([
                        'post_id' => $post->id,
                        'tag_id' => $tag->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
            
            $this->command->info('Tags seeded successfully.');
        }
    }
}
