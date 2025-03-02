<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsPublicToPetActivitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // First create the pet_activities table if it doesn't exist
        if (!Schema::hasTable('pet_activities')) {
            Schema::create('pet_activities', function (Blueprint $table) {
                $table->id();
                $table->foreignId('pet_id')->constrained()->onDelete('cascade');
                $table->string('activity_type', 20); // walk, play, vet, grooming, training, meal, medication, other
                $table->text('description')->nullable();
                $table->string('location')->nullable();
                $table->dateTime('happened_at');
                $table->string('image')->nullable();
                $table->boolean('is_public')->default(true);
                $table->timestamps();
                
                $table->index('pet_id');
                $table->index('activity_type');
                $table->index('happened_at');
            });
        } else {
            // If the table exists but doesn't have the is_public column, add it
            if (!Schema::hasColumn('pet_activities', 'is_public')) {
                Schema::table('pet_activities', function (Blueprint $table) {
                    $table->boolean('is_public')->default(true)->after('image');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('pet_activities')) {
            if (Schema::hasColumn('pet_activities', 'is_public')) {
                Schema::table('pet_activities', function (Blueprint $table) {
                    $table->dropColumn('is_public');
                });
            }
        }
    }
}
