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
        // Establish shared group categories for filtering and discovery experiences.
        Schema::create('group_categories', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('icon')->nullable();
            $table->string('color')->nullable();
            $table->text('description')->nullable();
            $table->unsignedInteger('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Primary groups table that anchors every social community space.
        Schema::create('groups', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('category')->nullable();
            $table->string('visibility')->default('open');
            $table->foreignId('creator_id')->constrained('users')->cascadeOnDelete();
            $table->string('cover_image')->nullable();
            $table->string('icon')->nullable();
            $table->text('rules')->nullable();
            $table->string('location')->nullable();
            $table->timestamps();
        });

        // Membership records capture role assignments and lifecycle events.
        Schema::create('group_members', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('group_id')->constrained('groups')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('role')->default('member');
            $table->string('status')->default('active');
            $table->timestamp('joined_at')->nullable();
            $table->timestamps();

            $table->unique(['group_id', 'user_id']);
        });

        // Discussion topics provide threaded conversations inside each group.
        Schema::create('group_topics', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->foreignId('group_id')->constrained('groups')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->boolean('is_pinned')->default(false);
            $table->boolean('is_locked')->default(false);
            $table->boolean('has_solution')->default(false);
            $table->timestamp('last_activity_at')->nullable();
            $table->unsignedInteger('views_count')->default(0);
            $table->timestamps();
        });

        // Replies drive conversational depth with hierarchical threading support.
        Schema::create('group_topic_replies', function (Blueprint $table): void {
            $table->id();
            $table->text('content');
            $table->foreignId('group_topic_id')->constrained('group_topics')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('group_topic_replies')->cascadeOnDelete();
            $table->boolean('is_solution')->default(false);
            $table->timestamps();
        });

        // Participant tracking powers notifications and read receipts for topics.
        Schema::create('group_topic_participants', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('group_topic_id')->constrained('group_topics')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['group_topic_id', 'user_id']);
        });

        // Events keep communities organised for in-person and virtual meetups.
        Schema::create('group_events', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('group_id')->constrained('groups')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
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

        // Attendance records power reminders and capacity management.
        Schema::create('group_event_attendees', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('group_event_id')->constrained('group_events')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('status')->default('going');
            $table->boolean('reminder_set')->default(false);
            $table->timestamps();

            $table->unique(['group_event_id', 'user_id']);
        });

        // Group-specific roles allow custom moderation and delegation structures.
        Schema::create('group_roles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('group_id')->constrained('groups')->cascadeOnDelete();
            $table->string('name');
            $table->string('color')->nullable();
            $table->text('description')->nullable();
            $table->text('permissions')->nullable();
            $table->integer('priority')->default(0);
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->unique(['group_id', 'name']);
        });

        // Pivot table linking members to bespoke group roles.
        Schema::create('group_user_roles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('group_user_id')->constrained('group_members')->cascadeOnDelete();
            $table->foreignId('group_role_id')->constrained('group_roles')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['group_user_id', 'group_role_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_user_roles');
        Schema::dropIfExists('group_roles');
        Schema::dropIfExists('group_event_attendees');
        Schema::dropIfExists('group_events');
        Schema::dropIfExists('group_topic_participants');
        Schema::dropIfExists('group_topic_replies');
        Schema::dropIfExists('group_topics');
        Schema::dropIfExists('group_members');
        Schema::dropIfExists('groups');
        Schema::dropIfExists('group_categories');
    }
};
