<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Pet;

class PetFriendshipsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (DB::table('pet_friendships')->count() === 0) {
            $pets = Pet::all();
            $statuses = ['pending', 'accepted', 'rejected'];
            
            foreach ($pets as $pet) {
                // Each pet has 0-5 friendships
                $friendCount = rand(0, 5);
                $friendPets = $pets->where('id', '!=', $pet->id)->random(min($friendCount, $pets->count() - 1));
                
                foreach ($friendPets as $friend) {
                    // Avoid duplicate friendships
                    $existingFriendship = DB::table('pet_friendships')
                        ->where(function ($query) use ($pet, $friend) {
                            $query->where('pet_id', $pet->id)
                                  ->where('friend_pet_id', $friend->id);
                        })
                        ->orWhere(function ($query) use ($pet, $friend) {
                            $query->where('pet_id', $friend->id)
                                  ->where('friend_pet_id', $pet->id);
                        })
                        ->exists();
                    
                    if (!$existingFriendship) {
                        $status = $statuses[rand(0, 2)];
                        
                        DB::table('pet_friendships')->insert([
                            'pet_id' => $pet->id,
                            'friend_pet_id' => $friend->id,
                            'status' => $status,
                            'created_at' => now()->subDays(rand(0, 30)),
                            'updated_at' => now()->subDays(rand(0, 15)),
                        ]);
                    }
                }
            }
            
            $this->command->info('Pet friendships seeded successfully.');
        }
    }
}
