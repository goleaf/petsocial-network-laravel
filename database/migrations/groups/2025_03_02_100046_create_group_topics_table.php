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
        Schema::create('group_topics', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->foreignId('group_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->boolean('is_pinned')->default(false);
            $table->boolean('is_locked')->default(false);
            $table->boolean('has_solution')->default(false);
            $table->timestamp('last_activity_at')->nullable();
            $table->unsignedInteger('views_count')->default(0);
            $table->timestamps();
        });

        Schema::create('group_topic_replies', function (Blueprint $table) {
            $table->id();
            $table->text('content');
            $table->foreignId('group_topic_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('parent_id')->nullable()->constrained('group_topic_replies')->onDelete('cascade');
            $table->boolean('is_solution')->default(false);
            $table->timestamps();
        });

        Schema::create('group_topic_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_topic_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            $table->unique(['group_topic_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_topic_participants');
        Schema::dropIfExists('group_topic_replies');
        Schema::dropIfExists('group_topics');
    }
};
