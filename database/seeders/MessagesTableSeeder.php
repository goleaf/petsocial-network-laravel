<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class MessagesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (DB::table('messages')->count() === 0) {
            $users = User::all();
            
            if ($users->count() < 2) {
                $this->command->info('Cannot seed messages: at least 2 users are required. Please seed users first.');
                return;
            }
            
            // Create conversations between pairs of users
            $conversationCount = min(10, $users->count() * ($users->count() - 1) / 2);
            $conversationPairs = [];
            
            for ($i = 0; $i < $conversationCount; $i++) {
                $sender = $users->random();
                $receiver = $users->except($sender->id)->random();
                
                // Ensure unique pairs
                $pairKey = min($sender->id, $receiver->id) . '-' . max($sender->id, $receiver->id);
                if (in_array($pairKey, $conversationPairs)) {
                    continue;
                }
                
                $conversationPairs[] = $pairKey;
                
                // Generate 3-15 messages for this conversation
                $messageCount = rand(3, 15);
                
                for ($j = 0; $j < $messageCount; $j++) {
                    // Alternate sender and receiver
                    $currentSender = $j % 2 == 0 ? $sender : $receiver;
                    $currentReceiver = $j % 2 == 0 ? $receiver : $sender;
                    
                    $createdAt = now()->subDays(rand(0, 30))->subHours(rand(0, 24));
                    $read = rand(0, 1) == 1; // 50% chance of being read
                    
                    DB::table('messages')->insert([
                        'sender_id' => $currentSender->id,
                        'receiver_id' => $currentReceiver->id,
                        'content' => "This is message #" . ($j + 1) . " in the conversation.",
                        'read' => $read,
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                    ]);
                }
            }
            
            $this->command->info('Messages seeded successfully.');
        }
    }
}
