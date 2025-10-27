<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

uses(TestCase::class)->in('Feature', 'Unit', 'Livewire', 'Filament', 'Http');

uses()->beforeEach(function () {
    Config::set('database.default', 'sqlite');
    Config::set('database.connections.sqlite', [
        'driver' => 'sqlite',
        'database' => ':memory:',
        'prefix' => '',
        'foreign_key_constraints' => true,
    ]);

    // Ensure every run starts from a clean schema tailored to the test component needs.
    Schema::dropIfExists('poll_votes');
    Schema::dropIfExists('poll_options');
    Schema::dropIfExists('polls');
    Schema::dropIfExists('group_events');
    Schema::dropIfExists('group_topics');
    Schema::dropIfExists('group_members');
    Schema::dropIfExists('groups');
    Schema::dropIfExists('group_categories');
    Schema::dropIfExists('attachments');
    Schema::dropIfExists('reports');
    Schema::dropIfExists('activity_logs');
    Schema::dropIfExists('post_reports');
    Schema::dropIfExists('posts');
    Schema::dropIfExists('comments');
    Schema::dropIfExists('comment_reports');
    Schema::dropIfExists('reactions');
    Schema::dropIfExists('shares');
    Schema::dropIfExists('pet_friendships');
    Schema::dropIfExists('pets');
    Schema::dropIfExists('friendships');
    Schema::dropIfExists('follows');
    Schema::dropIfExists('account_recoveries');
    Schema::dropIfExists('users');

    Schema::create('users', function (Blueprint $table): void {
        $table->id();
        $table->string('name');
        $table->string('email')->unique();
        $table->timestamp('email_verified_at')->nullable();
        $table->string('password');
        $table->rememberToken();
        $table->string('role')->default('user');
        $table->timestamp('suspended_at')->nullable();
        $table->timestamp('suspension_ends_at')->nullable();
        $table->text('suspension_reason')->nullable();
        // Notification preferences mirror the production JSON column to support preference hygiene tests.
        $table->json('notification_preferences')->nullable();
        $table->timestamps();
    });

    Schema::create('posts', function (Blueprint $table): void {
        // Core post metadata mirrors the production schema for compatibility in tests.
        $table->id();
        $table->foreignId('user_id');
        $table->text('content');
        $table->timestamps();
    });

    Schema::create('comments', function (Blueprint $table): void {
        // Comments link back to authors and posts to satisfy analytics relationships.
        $table->id();
        $table->foreignId('user_id');
        $table->foreignId('post_id');
        $table->text('content');
        $table->timestamps();
        $table->softDeletes();
    });

    Schema::create('post_reports', function (Blueprint $table): void {
        $table->id();
        $table->foreignId('user_id');
        $table->foreignId('post_id');
        $table->text('reason');
        $table->timestamps();
    });

    Schema::create('reactions', function (Blueprint $table): void {
        // Reaction tracking provides engagement metrics and post associations.
        $table->id();
        $table->foreignId('user_id');
        $table->foreignId('post_id');
        $table->string('type')->default('like');
        $table->timestamps();
    });

    Schema::create('comment_reports', function (Blueprint $table): void {
        // Comment reports support automated moderation thresholds in tests.
        $table->id();
        $table->foreignId('comment_id');
        $table->foreignId('user_id');
        $table->text('reason')->nullable();
        $table->timestamps();
    });

    Schema::create('shares', function (Blueprint $table): void {
        // Share records feed analytics for redistribution metrics.
        $table->id();
        $table->foreignId('user_id');
        $table->foreignId('post_id');
        $table->timestamps();
    });

    Schema::create('activity_logs', function (Blueprint $table): void {
        // Activity logs capture security events and moderation outcomes during tests.
        $table->id();
        $table->foreignId('user_id');
        $table->string('action');
        $table->string('description');
        $table->timestamps();
    });

    Schema::create('friendships', function (Blueprint $table): void {
        // Friendships enable analytics to compute social graph statistics.
        $table->id();
        $table->foreignId('sender_id');
        $table->foreignId('recipient_id');
        $table->string('status')->default('pending');
        $table->timestamp('accepted_at')->nullable();
        $table->timestamps();
    });

    Schema::create('follows', function (Blueprint $table): void {
        // Follow relationships supply follower counts for analytics growth tracking.
        $table->id();
        $table->foreignId('follower_id');
        $table->foreignId('followed_id');
        $table->boolean('notify')->default(true);
        $table->timestamps();
    });

    Schema::create('pets', function (Blueprint $table): void {
        // Pet records power pet-specific social features in tests.
        $table->id();
        $table->foreignId('user_id');
        $table->string('name');
        $table->string('type')->nullable();
        $table->string('breed')->nullable();
        $table->date('birthdate')->nullable();
        $table->string('avatar')->nullable();
        $table->string('location')->nullable();
        $table->text('bio')->nullable();
        $table->string('favorite_food')->nullable();
        $table->string('favorite_toy')->nullable();
        $table->boolean('is_public')->default(true);
        $table->timestamps();
    });

    Schema::create('pet_friendships', function (Blueprint $table): void {
        // Pet friendships mirror the bidirectional relationship layer for animals.
        $table->id();
        $table->foreignId('pet_id');
        $table->foreignId('friend_pet_id');
        $table->string('category')->nullable();
        $table->string('status')->default('accepted');
        $table->timestamp('accepted_at')->nullable();
        $table->timestamps();
    });

    Schema::create('account_recoveries', function (Blueprint $table): void {
        // Recovery logs mirror production auditing for password reset tracking.
        $table->id();
        $table->foreignId('user_id')->nullable();
        $table->string('email');
        $table->string('status');
        $table->string('token_identifier')->nullable();
        $table->timestamp('requested_at');
        $table->timestamp('completed_at')->nullable();
        $table->string('ip_address')->nullable();
        $table->text('user_agent')->nullable();
        $table->timestamps();
    });

    Schema::create('reports', function (Blueprint $table): void {
        // Reports table tracks moderation submissions across reportable entities.
        $table->id();
        $table->foreignId('user_id');
        $table->string('reportable_type');
        $table->unsignedBigInteger('reportable_id');
        $table->text('reason');
        $table->string('status')->default('pending');
        $table->text('notes')->nullable();
        $table->foreignId('resolved_by')->nullable();
        $table->timestamp('resolved_at')->nullable();
        $table->timestamps();
    });

    Schema::create('attachments', function (Blueprint $table): void {
        // Attachments mirror the topic uploader expectations by storing file metadata.
        $table->id();
        $table->morphs('attachable');
        $table->string('path');
        $table->string('filename');
        $table->string('mime_type')->nullable();
        $table->unsignedBigInteger('size')->default(0);
        $table->timestamps();
    });

    Schema::create('group_categories', function (Blueprint $table): void {
        // Group categories support optional taxonomy lookups for new groups.
        $table->id();
        $table->string('name');
        $table->string('slug')->unique();
        $table->timestamps();
    });

    Schema::create('groups', function (Blueprint $table): void {
        // Groups anchor community discussions and reference the creator profile.
        $table->id();
        $table->string('name');
        $table->string('slug')->unique();
        $table->text('description')->nullable();
        $table->foreignId('category_id')->nullable();
        $table->string('visibility')->default('open');
        $table->foreignId('creator_id');
        $table->boolean('is_active')->default(true);
        $table->timestamps();
        $table->softDeletes();
    });

    Schema::create('group_members', function (Blueprint $table): void {
        // Membership pivot stores per-user roles for the group component logic.
        $table->id();
        $table->foreignId('group_id');
        $table->foreignId('user_id');
        $table->string('role')->default('member');
        $table->string('status')->default('active');
        $table->timestamp('joined_at')->nullable();
        $table->timestamps();
    });

    Schema::create('group_topics', function (Blueprint $table): void {
        // Group topics persist the threaded discussions rendered by the Livewire view.
        $table->id();
        $table->foreignId('group_id');
        $table->foreignId('user_id');
        $table->string('title');
        $table->text('content');
        $table->boolean('is_pinned')->default(false);
        $table->boolean('is_locked')->default(false);
        $table->timestamps();
    });

    Schema::create('group_events', function (Blueprint $table): void {
        // Minimal event table satisfies the automatic withCount relation on Group.
        $table->id();
        $table->foreignId('group_id');
        $table->foreignId('user_id');
        $table->string('title');
        $table->timestamp('start_date');
        $table->timestamp('end_date')->nullable();
        $table->timestamps();
    });

    Schema::create('polls', function (Blueprint $table): void {
        // Polls align with the Livewire component's multiple choice configuration.
        $table->id();
        $table->foreignId('group_topic_id');
        $table->string('question');
        $table->boolean('multiple_choice')->default(false);
        $table->timestamp('expires_at')->nullable();
        $table->timestamps();
    });

    Schema::create('poll_options', function (Blueprint $table): void {
        // Poll options provide selectable answers for group discussions.
        $table->id();
        $table->foreignId('poll_id');
        $table->string('text');
        $table->timestamps();
    });

    Schema::create('poll_votes', function (Blueprint $table): void {
        // Votes table exists to satisfy foreign key dependencies during tests.
        $table->id();
        $table->foreignId('poll_option_id');
        $table->foreignId('user_id');
        $table->timestamps();
    });
});
