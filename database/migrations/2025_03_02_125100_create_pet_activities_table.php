<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePetActivitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Skip if the table already exists
        if (!Schema::hasTable('pet_activities')) {
            Schema::create('pet_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pet_id')->constrained()->onDelete('cascade');
            $table->string('activity_type', 20); // walk, play, vet, grooming, training, meal, medication, other
            $table->text('description')->nullable();
            $table->string('location')->nullable();
            $table->dateTime('happened_at');
            $table->string('image')->nullable();
            $table->timestamps();
            
            $table->index('pet_id');
            $table->index('activity_type');
            $table->index('happened_at');
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
        Schema::dropIfExists('pet_activities');
    }
}
