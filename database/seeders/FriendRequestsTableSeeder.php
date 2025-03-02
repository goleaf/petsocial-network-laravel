<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class FriendRequestsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (DB::table('friend_requests')->count() === 0) {
            $users = User::all();
            
            // Create pending friend requests
            $pendingRequestCount = min(15, $users->count());
            
            for ($i = 0; $i < $pendingRequestCount; $i++) {
                $sender = $users->random();
                $receiver = $users->except($sender->id)->random();
                
                // Check if friendship already exists
                $existingFriendship = DB::table('friendships')
                    ->where(function ($query) use ($sender, $receiver) {
                        $query->where('sender_id', $sender->id)
                              ->where('recipient_id', $receiver->id);
                    })
                    ->orWhere(function ($query) use ($sender, $receiver) {
                        $query->where('sender_id', $receiver->id)
                              ->where('recipient_id', $sender->id);
                    })
                    ->first();
                
                // Check if request already exists
                $existingRequest = DB::table('friend_requests')
                    ->where('sender_id', $sender->id)
                    ->where('receiver_id', $receiver->id)
                    ->first();
                
                if (!$existingFriendship && !$existingRequest) {
                    $createdAt = now()->subDays(rand(0, 15));
                    $categories = ['school', 'work', 'neighborhood', 'family', 'other'];
                    
                    DB::table('friend_requests')->insert([
                        'sender_id' => $sender->id,
                        'receiver_id' => $receiver->id,
                        'status' => 'pending',
                        'category' => $categories[array_rand($categories)],
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                    ]);
                }
            }
            
            // Create some accepted and declined requests
            $statuses = ['accepted', 'declined'];
            $resolvedRequestCount = min(10, $users->count());
            
            for ($i = 0; $i < $resolvedRequestCount; $i++) {
                $sender = $users->random();
                $receiver = $users->except($sender->id)->random();
                
                // Check if friendship already exists
                $existingFriendship = DB::table('friendships')
                    ->where(function ($query) use ($sender, $receiver) {
                        $query->where('sender_id', $sender->id)
                              ->where('recipient_id', $receiver->id);
                    })
                    ->orWhere(function ($query) use ($sender, $receiver) {
                        $query->where('sender_id', $receiver->id)
                              ->where('recipient_id', $sender->id);
                    })
                    ->first();
                
                // Check if request already exists
                $existingRequest = DB::table('friend_requests')
                    ->where('sender_id', $sender->id)
                    ->where('receiver_id', $receiver->id)
                    ->first();
                
                if (!$existingFriendship && !$existingRequest) {
                    $createdAt = now()->subDays(rand(16, 30));
                    $updatedAt = now()->subDays(rand(0, 15));
                    $status = $statuses[array_rand($statuses)];
                    $categories = ['school', 'work', 'neighborhood', 'family', 'other'];
                    
                    DB::table('friend_requests')->insert([
                        'sender_id' => $sender->id,
                        'receiver_id' => $receiver->id,
                        'status' => $status,
                        'category' => $categories[array_rand($categories)],
                        'created_at' => $createdAt,
                        'updated_at' => $updatedAt,
                    ]);
                    
                    // If accepted, create friendship
                    if ($status === 'accepted') {
                        DB::table('friendships')->insert([
                            'sender_id' => $sender->id,
                            'recipient_id' => $receiver->id,
                            'status' => 'accepted',
                            'accepted_at' => $updatedAt,
                            'created_at' => $updatedAt,
                            'updated_at' => $updatedAt,
                        ]);
                    }
                }
            }
            
            $this->command->info('Friend requests seeded successfully.');
        }
    }
}
