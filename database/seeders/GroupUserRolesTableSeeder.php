<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class GroupUserRolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (DB::table('group_user_roles')->count() === 0) {
            // Check if group_members and group_roles tables exist and have records
            $hasGroupMembers = Schema::hasTable('group_members') && DB::table('group_members')->count() > 0;
            $hasGroupRoles = Schema::hasTable('group_roles') && DB::table('group_roles')->count() > 0;
            
            if (!$hasGroupMembers || !$hasGroupRoles) {
                $this->command->info('Cannot seed group user roles: group_members or group_roles table is empty or does not exist. Please run GroupsTableSeeder and GroupRolesTableSeeder first.');
                return;
            }
            
            $groupMembers = DB::table('group_members')->get();
            $groupRoles = DB::table('group_roles')->get();
            
            // Group roles by group_id
            $rolesByGroup = [];
            foreach ($groupRoles as $role) {
                if (!isset($rolesByGroup[$role->group_id])) {
                    $rolesByGroup[$role->group_id] = [];
                }
                $rolesByGroup[$role->group_id][] = $role;
            }
            
            // For each group member, assign 1-2 roles
            foreach ($groupMembers as $member) {
                // Skip if no roles for this group
                if (!isset($rolesByGroup[$member->group_id]) || empty($rolesByGroup[$member->group_id])) {
                    continue;
                }
                
                $roles = $rolesByGroup[$member->group_id];
                $roleCount = rand(1, min(2, count($roles)));
                $selectedRoleIndices = array_rand($roles, $roleCount);
                
                // Convert to array if only one role selected
                if (!is_array($selectedRoleIndices)) {
                    $selectedRoleIndices = [$selectedRoleIndices];
                }
                
                foreach ($selectedRoleIndices as $index) {
                    $role = $roles[$index];
                    $createdAt = now()->subDays(rand(0, 30));
                    
                    // Use member ID as group_user_id and role ID as group_role_id
                    DB::table('group_user_roles')->insert([
                        'group_user_id' => $member->id,
                        'group_role_id' => $role->id,
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                    ]);
                }
            }
            
            $this->command->info('Group user roles seeded successfully.');
        }
    }
}
