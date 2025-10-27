<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations to create medical visit history.
     */
    public function up(): void
    {
        Schema::create('pet_medical_visits', function (Blueprint $table): void {
            // Relationships and core visit metadata
            $table->id();
            $table->foreignId('pet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('medical_record_id')->nullable()->constrained('pet_medical_records')->cascadeOnDelete();
            $table->date('visited_at');
            $table->string('veterinarian')->nullable();
            $table->string('clinic')->nullable();

            // Visit outcomes and diagnostics
            $table->string('reason_for_visit')->nullable();
            $table->text('diagnosis')->nullable();
            $table->text('treatment')->nullable();
            $table->text('prescriptions')->nullable();
            $table->text('follow_up_actions')->nullable();

            // Administrative tracking
            $table->boolean('requires_follow_up')->default(false);
            $table->date('follow_up_date')->nullable();
            $table->timestamps();

            $table->index(['pet_id', 'visited_at']);
        });
    }

    /**
     * Reverse the migrations for medical visit history.
     */
    public function down(): void
    {
        Schema::dropIfExists('pet_medical_visits');
    }
};
