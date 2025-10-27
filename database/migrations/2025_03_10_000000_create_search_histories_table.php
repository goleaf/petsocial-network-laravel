<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Class CreateSearchHistoriesTable
 *
 * This migration introduces the search_histories table which stores the queries executed by
 * authenticated users so the Advanced Search module can surface personalised history lists.
 */
return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('search_histories', function (Blueprint $table) {
            // Primary key for individual search history entries.
            $table->id();

            // Link each record to the user that executed the search.
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Store the raw query string and resolved search type for analytics and replay.
            $table->string('query');
            $table->string('search_type')->default('all');

            // Persist optional context for future refinements.
            $table->json('filters')->nullable();
            $table->unsignedInteger('results_count')->default(0);

            $table->timestamps();

            // Index the combination so we can de-duplicate efficiently.
            $table->index(['user_id', 'query']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('search_histories');
    }
};
