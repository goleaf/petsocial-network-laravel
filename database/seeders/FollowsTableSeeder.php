<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class FollowsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (DB::table('follows')->count() === 0) {
            $users = User::all();
            
            // Create user-to-user follows
            foreach ($users as $follower) {
                // Each user follows 1-5 other users
                $followCount = rand(1, min(5, $users->count() - 1));
                $potentialFollowees = $users->except($follower->id);
                
                if ($potentialFollowees->count() > 0) {
                    $followees = $potentialFollowees->random(min($followCount, $potentialFollowees->count()));
                    
                    foreach ($followees as $followee) {
                        $createdAt = now()->subDays(rand(0, 30));
                        
                        // Check if this follow relationship already exists
                        $existingFollow = DB::table('follows')
                            ->where('follower_id', $follower->id)
                            ->where('following_id', $followee->id)
                            ->first();
                            
                        if (!$existingFollow) {
                            DB::table('follows')->insert([
                                'follower_id' => $follower->id,
                                'following_id' => $followee->id,
                                'created_at' => $createdAt,
                                'updated_at' => $createdAt,
                            ]);
                        }
                    }
                }
            }
            
            $this->command->info('Follows seeded successfully.');
        }
    }
}
