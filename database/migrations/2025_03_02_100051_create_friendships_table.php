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
        if (!Schema::hasTable('friendships')) {
            Schema::create('friendships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('recipient_id')->constrained('users')->onDelete('cascade');
            $table->enum('status', ['pending', 'accepted', 'declined', 'blocked'])->default('pending');
            $table->string('category')->nullable(); // For categorizing friends (e.g., 'close friends', 'family', etc.)
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();
            
            // Ensure unique relationships
            $table->unique(['sender_id', 'recipient_id']);
        });
        }

        // Skip if follows table already exists
        if (!Schema::hasTable('follows')) {
            Schema::create('follows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('follower_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('followed_id')->constrained('users')->onDelete('cascade');
            $table->boolean('notify')->default(true); // Whether to notify the followed user
            $table->timestamps();
            
            // Ensure unique relationships
            $table->unique(['follower_id', 'followed_id']);
        });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('follows');
        Schema::dropIfExists('friendships');
    }
};
