<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

uses(TestCase::class)->in('Feature', 'Filament', 'Http', 'Livewire', 'Unit');

uses()->beforeEach(function () {
    $sqlitePath = database_path('testing.sqlite');

    Config::set('database.default', 'sqlite');
    Config::set('database.connections.sqlite', [
        'driver' => 'sqlite',
        'database' => $sqlitePath,
        'prefix' => '',
        'foreign_key_constraints' => true,
    ]);

    if (! file_exists($sqlitePath)) {
        // Create the sqlite stub so framework defaults pointing at database/testing.sqlite succeed when booting connections.
        touch($sqlitePath);
    }

    DB::purge('sqlite');
    DB::setDefaultConnection('sqlite');

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
    Schema::dropIfExists('group_events');
    Schema::dropIfExists('group_topics');
    Schema::dropIfExists('group_members');
    Schema::dropIfExists('groups');
    Schema::dropIfExists('group_categories');
    Schema::dropIfExists('follows');
    Schema::dropIfExists('friendships');
    Schema::dropIfExists('account_recoveries');
    Schema::dropIfExists('users');

    Schema::create('users', function (Blueprint $table) {
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

    Schema::create('posts', function (Blueprint $table) {
        // Core post metadata mirrors the production schema for compatibility in tests.
        $table->id();
        $table->foreignId('user_id');
        $table->text('content');
        $table->timestamps();
    });

    Schema::create('comments', function (Blueprint $table) {
        // Comments link back to authors and posts to satisfy analytics relationships.
        $table->id();
        $table->foreignId('user_id');
        $table->foreignId('post_id');
        $table->text('content');
        $table->timestamps();
        $table->softDeletes();
    });

    Schema::create('post_reports', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id');
        $table->foreignId('post_id');
        $table->text('reason');
        $table->timestamps();
    });

    Schema::create('reactions', function (Blueprint $table) {
        // Reaction tracking provides engagement metrics and post associations.
        $table->id();
        $table->foreignId('user_id');
        $table->foreignId('post_id');
        $table->string('type')->default('like');
        $table->timestamps();
    });

    Schema::create('comment_reports', function (Blueprint $table) {
        // Comment reports support automated moderation thresholds in tests.
        $table->id();
        $table->foreignId('comment_id');
        $table->foreignId('user_id');
        $table->text('reason')->nullable();
        $table->timestamps();
    });

    Schema::create('shares', function (Blueprint $table) {
        // Share records feed analytics for redistribution metrics.
        $table->id();
        $table->foreignId('user_id');
        $table->foreignId('post_id');
        $table->timestamps();
    });

    Schema::create('activity_logs', function (Blueprint $table) {
        // Activity logs capture security events and moderation outcomes during tests.
        $table->id();
        $table->foreignId('user_id');
        $table->string('action');
        $table->string('description');
        $table->timestamps();
    });

    Schema::create('friendships', function (Blueprint $table) {
        // Friendships enable analytics to compute social graph statistics.
        $table->id();
        $table->foreignId('sender_id');
        $table->foreignId('recipient_id');
        $table->string('status')->default('pending');
        $table->timestamp('accepted_at')->nullable();
        $table->timestamps();
    });

    Schema::create('follows', function (Blueprint $table) {
        // Follow relationships supply follower counts for analytics growth tracking.
        $table->id();
        $table->foreignId('follower_id');
        $table->foreignId('followed_id');
        $table->boolean('notify')->default(true);
        $table->timestamps();
    });

    Schema::create('pets', function (Blueprint $table) {
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

    Schema::create('pet_friendships', function (Blueprint $table) {
        // Pet friendships mirror the bidirectional relationship layer for animals.
        $table->id();
        $table->foreignId('pet_id');
        $table->foreignId('friend_pet_id');
        $table->string('category')->nullable();
        $table->string('status')->default('accepted');
        $table->timestamp('accepted_at')->nullable();
        $table->timestamps();
    });

    Schema::create('group_categories', function (Blueprint $table) {
        // Group categories replicate the production metadata structure for validation checks.
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

    Schema::create('groups', function (Blueprint $table) {
        // Group records fuel community features and provide mount targets for Livewire components.
        $table->id();
        $table->foreignId('category_id');
        $table->foreignId('creator_id');
        $table->string('name');
        $table->string('slug')->unique();
        $table->text('description')->nullable();
        $table->string('visibility')->default('open');
        $table->string('cover_image')->nullable();
        $table->string('icon')->nullable();
        $table->json('rules')->nullable();
        $table->string('location')->nullable();
        $table->boolean('is_active')->default(true);
        $table->timestamps();
        $table->softDeletes();
    });

    Schema::create('group_members', function (Blueprint $table) {
        // Group membership data powers withCount relationships while remaining lightweight for tests.
        $table->id();
        $table->foreignId('group_id');
        $table->foreignId('user_id');
        $table->string('role')->default('member');
        $table->timestamp('joined_at')->nullable();
        $table->string('status')->default('active');
        $table->timestamps();
    });

    Schema::create('group_topics', function (Blueprint $table) {
        // Group topics keep withCount('topics') queries satisfied when refreshing models during tests.
        $table->id();
        $table->foreignId('group_id');
        $table->foreignId('user_id');
        $table->string('title');
        $table->text('content')->nullable();
        $table->boolean('is_pinned')->default(false);
        $table->boolean('is_locked')->default(false);
        $table->timestamp('last_activity_at')->nullable();
        $table->unsignedBigInteger('views_count')->default(0);
        $table->timestamps();
    });

    Schema::create('group_events', function (Blueprint $table) {
        // Group events sustain withCount('events') for the group model without relying on full migrations.
        $table->id();
        $table->foreignId('group_id');
        $table->string('name');
        $table->timestamp('start_date')->nullable();
        $table->timestamp('end_date')->nullable();
        $table->string('location')->nullable();
        $table->text('description')->nullable();
        $table->timestamps();
    });

    Schema::create('account_recoveries', function (Blueprint $table) {
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

    Schema::create('reports', function (Blueprint $table) {
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
})->in('Feature');
