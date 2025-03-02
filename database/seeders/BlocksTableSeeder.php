<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class BlocksTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (DB::table('blocks')->count() === 0) {
            $users = User::all();
            
            // Create a few blocks between users
            $blockCount = min(10, $users->count() * 0.2); // Block about 20% of users
            
            for ($i = 0; $i < $blockCount; $i++) {
                $blocker = $users->random();
                $blocked = $users->except($blocker->id)->random();
                
                // Ensure this block doesn't already exist
                $existingBlock = DB::table('blocks')
                    ->where('blocker_id', $blocker->id)
                    ->where('blocked_id', $blocked->id)
                    ->first();
                
                if (!$existingBlock) {
                    $createdAt = now()->subDays(rand(0, 30));
                    
                    DB::table('blocks')->insert([
                        'blocker_id' => $blocker->id,
                        'blocked_id' => $blocked->id,
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                    ]);
                }
            }
            
            $this->command->info('Blocks seeded successfully.');
        }
    }
}
