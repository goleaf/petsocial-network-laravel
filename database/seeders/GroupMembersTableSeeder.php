<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Group;

class GroupMembersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (DB::table('group_members')->count() === 0) {
            $users = User::all();
            $groups = Group::all();
            
            if ($groups->count() == 0) {
                $this->command->info('No groups found. Please seed groups first.');
                return;
            }
            
            if ($users->count() == 0) {
                $this->command->info('No users found. Please seed users first.');
                return;
            }
            
            // For each group, add some random members
            foreach ($groups as $group) {
                // Get random number of members (between 3 and 15)
                $memberCount = rand(3, min(15, $users->count()));
                $members = $users->random($memberCount);
                
                foreach ($members as $member) {
                    // Check if this membership record already exists
                    $exists = DB::table('group_members')
                        ->where('group_id', $group->id)
                        ->where('user_id', $member->id)
                        ->exists();
                    
                    if (!$exists) {
                        $createdAt = now()->subDays(rand(0, 60));
                        
                        DB::table('group_members')->insert([
                            'group_id' => $group->id,
                            'user_id' => $member->id,
                            'joined_at' => $createdAt,
                            'created_at' => $createdAt,
                            'updated_at' => $createdAt,
                        ]);
                    }
                }
            }
            
            $this->command->info('Group members seeded successfully.');
        }
    }
}
