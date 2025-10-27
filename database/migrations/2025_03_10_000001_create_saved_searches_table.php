<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Class CreateSavedSearchesTable
 *
 * This migration provisions persistent saved searches for users so they can quickly replay
 * frequently used discovery filters across posts, users, pets, and tags.
 */
return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('saved_searches', function (Blueprint $table) {
            // Primary key for the saved search definition.
            $table->id();

            // Associate each saved search to a user account.
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Provide a label so the UI can render friendly names.
            $table->string('name');

            // Store the search query, context, and additional metadata.
            $table->string('query');
            $table->string('search_type')->default('all');
            $table->json('filters')->nullable();

            // Track how often a saved search is replayed to power analytics.
            $table->unsignedInteger('run_count')->default(0);

            $table->timestamps();

            // Enforce unique names per user to keep the list tidy.
            $table->unique(['user_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('saved_searches');
    }
};
