<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class GroupRolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (DB::table('group_roles')->count() === 0) {
            $groups = DB::table('groups')->get();
            
            if ($groups->count() == 0) {
                $this->command->info('No groups found. Please seed groups first.');
                return;
            }
            
            // For each group, create the default roles
            foreach ($groups as $group) {
                $roles = [
                    [
                        'group_id' => $group->id,
                        'name' => 'Admin',
                        'color' => '#FF5733',
                        'description' => 'Full control over the group',
                        'permissions' => json_encode([
                            'manage_members',
                            'manage_content',
                            'manage_settings',
                            'delete_group',
                            'pin_content',
                            'remove_content',
                            'ban_members',
                            'approve_members',
                            'create_events',
                            'create_polls',
                            'assign_roles',
                            'edit_group_info'
                        ]),
                        'priority' => 100,
                        'is_default' => false,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'group_id' => $group->id,
                        'name' => 'Moderator',
                        'color' => '#33A1FF',
                        'description' => 'Can manage content and members',
                        'permissions' => json_encode([
                            'manage_members',
                            'manage_content',
                            'pin_content',
                            'remove_content',
                            'ban_members',
                            'approve_members',
                            'create_events',
                            'create_polls'
                        ]),
                        'priority' => 50,
                        'is_default' => false,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'group_id' => $group->id,
                        'name' => 'Content Creator',
                        'color' => '#FFA500',
                        'description' => 'Can create and manage their own content',
                        'permissions' => json_encode([
                            'create_events',
                            'create_polls',
                            'edit_own_content'
                        ]),
                        'priority' => 25,
                        'is_default' => false,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'group_id' => $group->id,
                        'name' => 'Member',
                        'color' => '#33FF57',
                        'description' => 'Standard member with basic permissions',
                        'permissions' => json_encode([
                            'view_content',
                            'create_topics',
                            'reply_to_topics',
                            'join_events'
                        ]),
                        'priority' => 0,
                        'is_default' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'group_id' => $group->id,
                        'name' => 'Read-Only',
                        'color' => '#CCCCCC',
                        'description' => 'Can only view content, cannot post',
                        'permissions' => json_encode([
                            'view_content'
                        ]),
                        'priority' => -10,
                        'is_default' => false,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                ];
                
                DB::table('group_roles')->insert($roles);
                
                // We'll skip assigning roles to group members for now since the group_user_roles table
                // doesn't exist in our migrations yet. This will be handled by a separate migration.
            }
            
            $this->command->info('Group roles seeded successfully.');
        }
    }
}
