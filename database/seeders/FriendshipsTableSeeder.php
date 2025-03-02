<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class FriendshipsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (DB::table('friendships')->count() === 0) {
            $users = User::all();
            $statuses = ['pending', 'accepted', 'declined', 'blocked'];
            
            foreach ($users as $user) {
                // Each user has 0-7 friendships
                $friendCount = rand(0, 7);
                $friendUsers = $users->where('id', '!=', $user->id)->random(min($friendCount, $users->count() - 1));
                
                foreach ($friendUsers as $friend) {
                    // Avoid duplicate friendships
                    $existingFriendship = DB::table('friendships')
                        ->where(function ($query) use ($user, $friend) {
                            $query->where('sender_id', $user->id)
                                  ->where('recipient_id', $friend->id);
                        })
                        ->orWhere(function ($query) use ($user, $friend) {
                            $query->where('sender_id', $friend->id)
                                  ->where('recipient_id', $user->id);
                        })
                        ->exists();
                    
                    if (!$existingFriendship) {
                        $status = $statuses[rand(0, 3)];
                        
                        DB::table('friendships')->insert([
                            'sender_id' => $user->id,
                            'recipient_id' => $friend->id,
                            'status' => $status,
                            'created_at' => now()->subDays(rand(0, 30)),
                            'updated_at' => now()->subDays(rand(0, 15)),
                        ]);
                    }
                }
            }
            
            $this->command->info('Friendships seeded successfully.');
        }
    }
}
