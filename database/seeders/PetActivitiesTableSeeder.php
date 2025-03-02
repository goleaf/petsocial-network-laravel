<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Pet;
use App\Models\User;

class PetActivitiesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (DB::table('pet_activities')->count() === 0) {
            $pets = Pet::all();
            $users = User::all();
            $activityTypes = ['walk', 'play', 'feed', 'groom', 'vet', 'medication', 'training'];
            
            foreach ($pets as $pet) {
                // Each pet has 1-10 activities
                for ($i = 0; $i < rand(1, 10); $i++) {
                    $activityType = $activityTypes[rand(0, count($activityTypes) - 1)];
                    $isPublic = (bool)rand(0, 1);
                    
                    $activityData = [
                        'pet_id' => $pet->id,
                        'activity_type' => $activityType,
                        'description' => "A {$activityType} activity for {$pet->name}",
                        'location' => rand(0, 1) ? ['Park', 'Home', 'Beach', 'Vet Clinic', 'Pet Store'][rand(0, 4)] : null,
                        'happened_at' => now()->subDays(rand(0, 30))->subHours(rand(0, 24)),
                        'is_public' => $isPublic,
                        'created_at' => now()->subDays(rand(0, 30))->subHours(rand(0, 24)),
                        'updated_at' => now()->subDays(rand(0, 15))->subHours(rand(0, 24)),
                    ];
                    
                    // Check if the table has the polymorphic columns
                    if (Schema::hasColumn('pet_activities', 'activityable_type') && 
                        Schema::hasColumn('pet_activities', 'activityable_id')) {
                        $activityData['activityable_type'] = 'App\\Models\\Pet';
                        $activityData['activityable_id'] = $pet->id;
                    }
                    
                    // Check if the table has user_id column
                    if (Schema::hasColumn('pet_activities', 'user_id')) {
                        $activityData['user_id'] = $pet->user_id;
                    }
                    
                    DB::table('pet_activities')->insert($activityData);
                }
            }
            
            $this->command->info('Pet activities seeded successfully.');
        }
    }
}
