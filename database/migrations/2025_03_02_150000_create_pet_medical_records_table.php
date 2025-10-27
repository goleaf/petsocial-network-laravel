<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations to create pet medical records.
     */
    public function up(): void
    {
        Schema::create('pet_medical_records', function (Blueprint $table): void {
            // Basic identifiers and relationships
            $table->id();
            $table->foreignId('pet_id')->constrained()->cascadeOnDelete();

            // Veterinary and insurance details
            $table->string('primary_veterinarian')->nullable();
            $table->string('clinic_name')->nullable();
            $table->string('clinic_contact')->nullable();
            $table->string('insurance_provider')->nullable();
            $table->string('insurance_policy_number')->nullable();

            // Health tracking information
            $table->date('last_checkup_at')->nullable();
            $table->text('known_conditions')->nullable();
            $table->text('medications')->nullable();
            $table->text('allergies')->nullable();
            $table->text('vaccination_status')->nullable();
            $table->string('microchip_id')->nullable();
            $table->text('dietary_notes')->nullable();
            $table->text('emergency_plan')->nullable();

            $table->timestamps();

            $table->unique('pet_id');
        });
    }

    /**
     * Reverse the migrations for pet medical records.
     */
    public function down(): void
    {
        Schema::dropIfExists('pet_medical_records');
    }
};
