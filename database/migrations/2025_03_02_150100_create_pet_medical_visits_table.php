<?php

use Illuminate\\Database\\Migrations\\Migration;
use Illuminate\\Database\\Schema\\Blueprint;
use Illuminate\\Support\\Facades\\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create table that stores historical veterinary visit data linked to medical records
        Schema::create('pet_medical_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medical_record_id')->constrained('pet_medical_records')->cascadeOnDelete();
            $table->date('visit_date')->nullable();
            $table->string('veterinarian')->nullable();
            $table->string('reason')->nullable();
            $table->string('diagnosis')->nullable();
            $table->text('treatment')->nullable();
            $table->text('medications_prescribed')->nullable();
            $table->date('follow_up_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove visit history table on rollback to keep database clean
        Schema::dropIfExists('pet_medical_visits');
    }
};
