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
        Schema::create('group_events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('group_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamp('start_date');
            $table->timestamp('end_date')->nullable();
            $table->string('location')->nullable();
            $table->string('location_url')->nullable();
            $table->boolean('is_online')->default(false);
            $table->string('online_meeting_url')->nullable();
            $table->string('cover_image')->nullable();
            $table->unsignedInteger('max_attendees')->nullable();
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });

        Schema::create('group_event_attendees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_event_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['going', 'interested', 'not_going'])->default('going');
            $table->boolean('reminder_set')->default(false);
            $table->timestamps();
            $table->unique(['group_event_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_event_attendees');
        Schema::dropIfExists('group_events');
    }
};
