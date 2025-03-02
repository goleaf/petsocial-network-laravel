<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Post;

class ReactionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (DB::table('reactions')->count() === 0) {
            $users = User::all();
            $posts = Post::all();
            $reactionTypes = ['like', 'love', 'haha', 'wow', 'sad', 'angry'];
            
            // Post reactions
            foreach ($posts as $post) {
                // Each post gets 0-10 reactions
                $reactionCount = rand(0, 10);
                $reactingUsers = $users->random(min($reactionCount, $users->count()));
                
                foreach ($reactingUsers as $user) {
                    DB::table('reactions')->insert([
                        'user_id' => $user->id,
                        'post_id' => $post->id,
                        'type' => $reactionTypes[rand(0, count($reactionTypes) - 1)],
                        'created_at' => now()->subDays(rand(0, 30))->subHours(rand(0, 24)),
                        'updated_at' => now()->subDays(rand(0, 30))->subHours(rand(0, 24)),
                    ]);
                }
            }
            
            $this->command->info('Reactions seeded successfully.');
        }
    }
}
