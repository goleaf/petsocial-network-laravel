<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('group_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('color')->nullable();
            $table->text('description')->nullable();
            $table->json('permissions')->nullable();
            $table->integer('priority')->default(0);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            
            // Each role name must be unique within a group
            $table->unique(['group_id', 'name']);
        });

        Schema::create('group_user_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_user_id')->constrained('group_members')->onDelete('cascade');
            $table->foreignId('group_role_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            // Each user can have a role only once
            $table->unique(['group_user_id', 'group_role_id']);
        });
        
        // Insert default roles for existing groups
        DB::table('groups')->orderBy('id')->chunk(100, function ($groups) {
            foreach ($groups as $group) {
                // Create default roles for each group
                DB::table('group_roles')->insert([
                    [
                        'group_id' => $group->id,
                        'name' => 'Admin',
                        'color' => '#FF5733',
                        'description' => 'Group administrators with full permissions',
                        'permissions' => json_encode([
                            'manage_group' => true,
                            'manage_members' => true,
                            'manage_roles' => true,
                            'manage_topics' => true,
                            'manage_events' => true,
                            'pin_topics' => true,
                            'approve_members' => true,
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
                        'description' => 'Group moderators with limited administrative permissions',
                        'permissions' => json_encode([
                            'manage_members' => true,
                            'manage_topics' => true,
                            'pin_topics' => true,
                            'approve_members' => true,
                        ]),
                        'priority' => 50,
                        'is_default' => false,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'group_id' => $group->id,
                        'name' => 'Member',
                        'color' => '#33FF57',
                        'description' => 'Regular group members',
                        'permissions' => json_encode([]),
                        'priority' => 0,
                        'is_default' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                ]);
                
                // Assign admin role to group creator
                $adminRole = DB::table('group_roles')
                    ->where('group_id', $group->id)
                    ->where('name', 'Admin')
                    ->first();
                
                if ($adminRole) {
                    $groupUser = DB::table('group_user')
                        ->where('group_id', $group->id)
                        ->where('user_id', $group->creator_id)
                        ->first();
                    
                    if ($groupUser) {
                        DB::table('group_user_roles')->insert([
                            'group_user_id' => $groupUser->id,
                            'group_role_id' => $adminRole->id,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_user_roles');
        Schema::dropIfExists('group_roles');
    }
};
