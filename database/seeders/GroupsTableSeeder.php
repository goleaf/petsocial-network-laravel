<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Group\Category;
use App\Models\Group\Group;
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
            $categories = Category::getActiveCategories();

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
                $category = $categories->random();

                $group = Group::create([
                    'name' => $groupName . ' ' . ($i + 1),
                    'slug' => Group::generateUniqueSlug($groupName . ' ' . ($i + 1)),
                    'description' => 'This is a group for ' . strtolower($category->name) . ' to connect and share experiences.',
                    'category_id' => $category->id,
                    'visibility' => collect(['open', 'closed', 'secret'])->random(),
                    'creator_id' => $creator->id,
                    'rules' => [
                        'Be respectful to all members',
                        'No spam or self-promotion',
                        'Keep discussions relevant to the group topic',
                        'No hate speech or bullying',
                    ],
                    'location' => fake()->city(),
                    'created_at' => now()->subMonths(rand(1, 6)),
                    'updated_at' => now(),
                ]);

                // Add creator as admin with an explicit membership record.
                $group->members()->syncWithoutDetaching([
                    $creator->id => [
                        'role' => 'admin',
                        'status' => 'active',
                        'joined_at' => now()->subMonths(rand(1, 6)),
                    ],
                ]);

                // Add 5-15 random members
                $members = $users->where('id', '!=', $creator->id)->random(rand(5, min(15, $users->count() - 1)));
                foreach ($members as $member) {
                    $group->members()->syncWithoutDetaching([
                        $member->id => [
                            'role' => rand(0, 10) > 8 ? 'moderator' : 'member',
                            'status' => 'active',
                            'joined_at' => now()->subMonths(rand(0, 5)),
                        ],
                    ]);
                }
            }

            $this->command->info('Groups seeded successfully.');
        }
    }
}
