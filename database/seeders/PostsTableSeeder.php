<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;
use App\Models\User;
use App\Models\Pet;
use App\Models\Post;

class PostsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (DB::table('posts')->count() === 0) {
            $users = User::all();
            $pets = Pet::all();
            
            // User posts
            foreach ($users as $user) {
                // Each user creates 1-5 posts
                for ($i = 0; $i < rand(1, 5); $i++) {
                    Post::create([
                        'user_id' => $user->id,
                        'pet_id' => null,
                        'content' => "This is a post by {$user->name}. Post number $i.",
                        'visibility' => Arr::random(['public', 'friends', 'private']), // Seed a variety of privacy settings
                        'created_at' => now()->subDays(rand(0, 30))->subHours(rand(0, 24)),
                        'updated_at' => now()->subDays(rand(0, 30))->subHours(rand(0, 24)),
                    ]);
                }
            }
            
            // Pet posts
            foreach ($pets as $pet) {
                // Each pet has 0-3 posts
                for ($i = 0; $i < rand(0, 3); $i++) {
                    Post::create([
                        'user_id' => $pet->user_id,
                        'pet_id' => $pet->id,
                        'content' => "This is a post by {$pet->name} (a {$pet->type}). Post number $i.",
                        'visibility' => Arr::random(['public', 'friends', 'private']), // Mirror human posts with mixed privacy options
                        'created_at' => now()->subDays(rand(0, 30))->subHours(rand(0, 24)),
                        'updated_at' => now()->subDays(rand(0, 30))->subHours(rand(0, 24)),
                    ]);
                }
            }
            
            $this->command->info('Posts seeded successfully.');
        }
    }
}
