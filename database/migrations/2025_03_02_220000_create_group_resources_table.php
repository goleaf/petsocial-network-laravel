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
        Schema::create('group_resources', function (Blueprint $table): void {
            // Primary key for the shared resource entry.
            $table->id();
            // Reference back to the owning group so entries stay scoped to a community.
            $table->foreignId('group_id')->constrained('groups')->cascadeOnDelete();
            // Track which member contributed the resource for audit trails and permissions.
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            // Human readable title surfaced inside the UI resource list.
            $table->string('title');
            // Optional description to give members context about the shared material.
            $table->text('description')->nullable();
            // Resource classification toggles validation logic (e.g. link vs document).
            $table->string('type');
            // Persist URLs for link-based shares while staying null for uploaded documents.
            $table->string('url')->nullable();
            // Store the document path when uploads accompany the resource entry.
            $table->string('file_path')->nullable();
            // Record metadata about uploaded documents for display and download helpers.
            $table->string('file_name')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('file_mime')->nullable();
            // Indexes speed up queries when filtering resource feeds by group or type.
            $table->index(['group_id', 'type']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_resources');
    }
};
