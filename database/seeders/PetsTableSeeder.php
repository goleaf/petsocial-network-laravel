<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Pet;
use Illuminate\Support\Str;

class PetsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (DB::table('pets')->count() === 0) {
            $users = User::all();
            $petTypes = ['dog', 'cat', 'bird', 'fish', 'rabbit', 'hamster', 'turtle'];
            $breeds = [
                'dog' => ['Labrador', 'German Shepherd', 'Bulldog', 'Poodle', 'Beagle'],
                'cat' => ['Persian', 'Siamese', 'Maine Coon', 'Ragdoll', 'Bengal'],
                'bird' => ['Parrot', 'Canary', 'Finch', 'Cockatiel', 'Lovebird'],
                'fish' => ['Goldfish', 'Betta', 'Guppy', 'Angelfish', 'Tetra'],
                'rabbit' => ['Holland Lop', 'Mini Rex', 'Dutch', 'Lionhead', 'Flemish Giant'],
                'hamster' => ['Syrian', 'Dwarf Campbell', 'Winter White', 'Roborovski', 'Chinese'],
                'turtle' => ['Red-Eared Slider', 'Box Turtle', 'Painted Turtle', 'Map Turtle', 'Musk Turtle']
            ];
            
            foreach ($users as $user) {
                // Each user has 1-3 pets
                for ($i = 0; $i < rand(1, 3); $i++) {
                    $type = $petTypes[rand(0, count($petTypes) - 1)];
                    $breed = $breeds[$type][rand(0, count($breeds[$type]) - 1)];
                    $birthdate = now()->subYears(rand(1, 15))->subDays(rand(0, 365));
                    
                    Pet::create([
                        'user_id' => $user->id,
                        'name' => ['Buddy', 'Max', 'Charlie', 'Lucy', 'Bailey', 'Cooper', 'Daisy', 'Rocky', 'Lola', 'Sadie'][rand(0, 9)],
                        'type' => $type,
                        'breed' => $breed,
                        'birthdate' => $birthdate,
                        'avatar' => rand(0, 1) ? "pet_{$user->id}_{$i}.jpg" : null,
                        'location' => rand(0, 1) ? ['New York', 'Los Angeles', 'Chicago', 'Houston', 'Phoenix'][rand(0, 4)] : null,
                        'bio' => "A lovely $breed $type who loves to play and cuddle.",
                        'favorite_food' => rand(0, 1) ? ['Kibble', 'Wet Food', 'Treats', 'Fish', 'Chicken'][rand(0, 4)] : null,
                        'favorite_toy' => rand(0, 1) ? ['Ball', 'Rope', 'Stuffed Animal', 'Laser Pointer', 'Feather Wand'][rand(0, 4)] : null,
                        'is_public' => (bool)rand(0, 1),
                    ]);
                }
            }
            
            $this->command->info('Pets seeded successfully.');
        }
    }
}
