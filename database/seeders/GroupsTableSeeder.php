<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class GroupsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (DB::table('groups')->count() === 0) {
            $users = User::all();
            
            $groupCategories = [
                'Pet Owners',
                'Dog Lovers',
                'Cat Enthusiasts',
                'Bird Watchers',
                'Exotic Pets',
                'Pet Health',
                'Pet Training',
                'Pet Adoption',
                'Pet Nutrition',
                'Pet Photography'
            ];
            
            $groupNames = [
                'Happy Paws',
                'Furry Friends',
                'Purrfect Companions',
                'Bark & Play',
                'Feathered Friends',
                'Exotic Pet Lovers',
                'Healthy Pets Club',
                'Training Tips & Tricks',
                'Rescue & Adopt',
                'Pet Nutrition Experts',
                'Pet Photographers',
                'Pet Travel Adventures',
                'Senior Pet Care',
                'Puppy Playdates',
                'Kitten Corner'
            ];
            
            // Create 10 groups
            for ($i = 0; $i < 10; $i++) {
                $creator = $users->random();
                $groupName = $groupNames[array_rand($groupNames)];
                $category = $groupCategories[array_rand($groupCategories)];
                
                $groupId = DB::table('groups')->insertGetId([
                    'name' => $groupName . ' ' . ($i + 1),
                    'description' => 'This is a group for ' . strtolower($category) . ' to connect and share experiences.',
                    'category' => $category,
                    'visibility' => ['open', 'closed', 'secret'][rand(0, 2)],
                    'creator_id' => $creator->id,
                    'rules' => json_encode([
                        'Be respectful to all members',
                        'No spam or self-promotion',
                        'Keep discussions relevant to the group topic',
                        'No hate speech or bullying'
                    ]),
                    'created_at' => now()->subMonths(rand(1, 6)),
                    'updated_at' => now(),
                ]);
                
                // Add creator as admin
                DB::table('group_members')->insert([
                    'group_id' => $groupId,
                    'user_id' => $creator->id,
                    'role' => 'admin',
                    'status' => 'active',
                    'joined_at' => now()->subMonths(rand(1, 6)),
                    'created_at' => now()->subMonths(rand(1, 6)),
                    'updated_at' => now(),
                ]);
                
                // Add 5-15 random members
                $members = $users->where('id', '!=', $creator->id)->random(rand(5, min(15, $users->count() - 1)));
                foreach ($members as $member) {
                    DB::table('group_members')->insert([
                        'group_id' => $groupId,
                        'user_id' => $member->id,
                        'role' => rand(0, 10) > 8 ? 'moderator' : 'member',
                        'status' => 'active',
                        'joined_at' => now()->subMonths(rand(0, 5)),
                        'created_at' => now()->subMonths(rand(0, 5)),
                        'updated_at' => now(),
                    ]);
                }
            }
            
            $this->command->info('Groups seeded successfully.');
        }
    }
}
