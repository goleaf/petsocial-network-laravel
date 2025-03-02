<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPolymorphicRelationsToActivities extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Update pet_activities table
        if (Schema::hasTable('pet_activities') && !Schema::hasColumns('pet_activities', ['type', 'data', 'actor_type', 'actor_id', 'target_type', 'target_id', 'read'])) {
            Schema::table('pet_activities', function (Blueprint $table) {
                // Add new columns if they don't exist
                if (!Schema::hasColumn('pet_activities', 'type')) {
                    $table->string('type')->nullable()->after('activity_type');
                }
                
                if (!Schema::hasColumn('pet_activities', 'data')) {
                    $table->json('data')->nullable()->after('description');
                }
                
                if (!Schema::hasColumn('pet_activities', 'actor_type')) {
                    $table->string('actor_type')->nullable()->after('data');
                }
                
                if (!Schema::hasColumn('pet_activities', 'actor_id')) {
                    $table->unsignedBigInteger('actor_id')->nullable()->after('actor_type');
                }
                
                if (!Schema::hasColumn('pet_activities', 'target_type')) {
                    $table->string('target_type')->nullable()->after('actor_id');
                }
                
                if (!Schema::hasColumn('pet_activities', 'target_id')) {
                    $table->unsignedBigInteger('target_id')->nullable()->after('target_type');
                }
                
                if (!Schema::hasColumn('pet_activities', 'read')) {
                    $table->boolean('read')->default(false)->after('is_public');
                }
            });
        }
        
        // Create user_activities table if it doesn't exist
        if (!Schema::hasTable('user_activities')) {
            Schema::create('user_activities', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->string('type')->nullable();
                $table->text('description')->nullable();
                $table->json('data')->nullable();
                $table->string('actor_type')->nullable();
                $table->unsignedBigInteger('actor_id')->nullable();
                $table->string('target_type')->nullable();
                $table->unsignedBigInteger('target_id')->nullable();
                $table->boolean('read')->default(false);
                $table->timestamps();
                
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Remove columns from pet_activities table
        Schema::table('pet_activities', function (Blueprint $table) {
            if (Schema::hasColumn('pet_activities', 'type')) {
                $table->dropColumn('type');
            }
            
            if (Schema::hasColumn('pet_activities', 'data')) {
                $table->dropColumn('data');
            }
            
            if (Schema::hasColumn('pet_activities', 'actor_type')) {
                $table->dropColumn('actor_type');
            }
            
            if (Schema::hasColumn('pet_activities', 'actor_id')) {
                $table->dropColumn('actor_id');
            }
            
            if (Schema::hasColumn('pet_activities', 'target_type')) {
                $table->dropColumn('target_type');
            }
            
            if (Schema::hasColumn('pet_activities', 'target_id')) {
                $table->dropColumn('target_id');
            }
            
            if (Schema::hasColumn('pet_activities', 'read')) {
                $table->dropColumn('read');
            }
        });
        
        // Drop user_activities table if it exists
        if (Schema::hasTable('user_activities')) {
            Schema::dropIfExists('user_activities');
        }
    }
}
