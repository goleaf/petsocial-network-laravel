<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

// Ensure every test suite has access to the full Laravel application context.
uses(TestCase::class)->in('Feature', 'Livewire', 'Unit', 'Filament', 'Http');

function prepareTestDatabase(): void
{
    Config::set('database.default', 'sqlite');
    Config::set('database.connections.sqlite', [
        'driver' => 'sqlite',
        'database' => ':memory:',
        'prefix' => '',
        'foreign_key_constraints' => true,
    ]);

    // Reset the connection so the in-memory database persists for the full request lifecycle.
    DB::purge('sqlite');
    DB::reconnect('sqlite');

    // Individual test files invoke this helper to guarantee an isolated database snapshot for each assertion run.

    // Use the SQLite schema builder directly so all operations share the same in-memory connection.
    $schema = Schema::connection('sqlite');

    // Drop existing tables to guarantee a clean slate before rebuilding fixtures for each test.
    foreach ([
        'reports',
        'activity_logs',
        'blocks',
        'post_reports',
        'post_tag',
        'tags',
        'posts',
        'comments',
        'comment_reports',
        'reactions',
        'shares',
        'pet_friendships',
        'pets',
        'friendships',
        'messages',
        'group_event_attendees',
        'group_events',
        'group_topic_participants',
        'group_topics',
        'group_members',
        'groups',
        'group_categories',
        'account_recoveries',
        'notifications',
        'follows',
        'users',
    ] as $table) {
        $schema->dropIfExists($table);
    }

    $schema->create('users', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        // Usernames support friend export lookups and Livewire filters.
        $table->string('username')->nullable();
        $table->string('email')->unique();
        $table->timestamp('email_verified_at')->nullable();
        $table->string('password');
        $table->rememberToken();
        $table->string('role')->default('user');
        // Contact metadata is required when exporting friend lists to CSV/VCF formats.
        $table->string('phone')->nullable();
        $table->string('avatar')->nullable();
        // Visibility flags mirror the production schema so settings tests can persist preferences.
        $table->string('profile_visibility')->default('public');
        $table->string('posts_visibility')->default('public');
        // JSON columns carry per-section visibility choices for the privacy controls.
        $table->json('privacy_settings')->nullable();
        $table->timestamp('suspended_at')->nullable();
        $table->timestamp('suspension_ends_at')->nullable();
        $table->text('suspension_reason')->nullable();
        // Mirror the production schema so login gating logic can check the flag reliably.
        $table->timestamp('deactivated_at')->nullable();
        // Notification preferences mirror the production JSON column to support preference hygiene tests.
        $table->json('notification_preferences')->nullable();
        // The component renders conditional UI when two-factor authentication is enabled on the account.
        $table->boolean('two_factor_enabled')->default(false);
        $table->timestamps();
    });

    $schema->create('group_categories', function (Blueprint $table) {
        // Group categories provide taxonomy metadata for organizing social spaces.
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

    $schema->create('groups', function (Blueprint $table) {
        // Group records mirror the production schema needed for editing scenarios.
        $table->id();
        $table->string('name');
        $table->string('slug')->unique();
        $table->text('description');
        $table->unsignedBigInteger('category_id');
        $table->string('visibility')->default('open');
        $table->unsignedBigInteger('creator_id')->nullable();
        $table->string('cover_image')->nullable();
        $table->string('icon')->nullable();
        $table->json('rules')->nullable();
        $table->string('location')->nullable();
        $table->boolean('is_active')->default(true);
        $table->timestamps();
        $table->softDeletes();
    });

    $schema->create('group_members', function (Blueprint $table) {
        // Membership pivot keeps track of roles and join statuses for groups.
        $table->id();
        $table->unsignedBigInteger('group_id');
        $table->unsignedBigInteger('user_id');
        $table->string('role')->default('member');
        $table->string('status')->default('active');
        $table->timestamp('joined_at')->nullable();
        $table->timestamps();
    });

    $schema->create('group_topics', function (Blueprint $table) {
        // Topics exist so automatic withCount queries on the Group model succeed.
        $table->id();
        $table->unsignedBigInteger('group_id');
        $table->unsignedBigInteger('user_id')->nullable();
        $table->string('title');
        $table->text('content')->nullable();
        $table->boolean('is_pinned')->default(false);
        $table->boolean('is_locked')->default(false);
        $table->timestamp('last_activity_at')->nullable();
        $table->unsignedBigInteger('views_count')->default(0);
        $table->timestamps();
    });

    $schema->create('group_topic_participants', function (Blueprint $table) {
        // Topic participation records back fill Livewire analytics without foreign keys.
        $table->id();
        $table->unsignedBigInteger('group_topic_id');
        $table->unsignedBigInteger('user_id');
        $table->timestamps();
    });

    $schema->create('group_events', function (Blueprint $table) {
        // Event records enable group withCount metadata for calendar features.
        $table->id();
        $table->unsignedBigInteger('group_id');
        $table->unsignedBigInteger('user_id')->nullable();
        $table->string('title');
        $table->text('description')->nullable();
        $table->timestamp('start_date')->nullable();
        $table->timestamp('end_date')->nullable();
        $table->string('location')->nullable();
        $table->string('location_url')->nullable();
        $table->boolean('is_online')->default(false);
        $table->string('online_meeting_url')->nullable();
        $table->string('cover_image')->nullable();
        $table->unsignedInteger('max_attendees')->nullable();
        $table->boolean('is_published')->default(false);
        $table->timestamps();
    });

    $schema->create('group_event_attendees', function (Blueprint $table) {
        // Event attendance pivot exists to satisfy relationships when counting attendees.
        $table->id();
        $table->unsignedBigInteger('group_event_id');
        $table->unsignedBigInteger('user_id');
        $table->string('status')->default('interested');
        $table->boolean('reminder_set')->default(false);
        $table->timestamps();
    });

    $schema->create('posts', function (Blueprint $table) {
        // Core post metadata mirrors the production schema for compatibility in tests.
        $table->id();
        $table->foreignId('user_id');
        $table->text('content');
        $table->timestamps();
    });

    $schema->create('tags', function (Blueprint $table) {
        // Tag records power trending widgets embedded within group detail pages.
        $table->id();
        $table->string('name');
        $table->timestamps();
    });

    $schema->create('post_tag', function (Blueprint $table) {
        // Pivot table connects posts and tags for popularity calculations.
        $table->id();
        $table->foreignId('post_id');
        $table->foreignId('tag_id');
        $table->timestamps();
    });

    $schema->create('comments', function (Blueprint $table) {
        // Comments link back to authors and posts to satisfy analytics relationships.
        $table->id();
        $table->foreignId('user_id');
        $table->foreignId('post_id');
        $table->text('content');
        $table->timestamps();
        $table->softDeletes();
    });

    $schema->create('post_reports', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id');
        $table->foreignId('post_id');
        $table->text('reason');
        $table->timestamps();
    });

    $schema->create('reactions', function (Blueprint $table) {
        // Reaction tracking provides engagement metrics and post associations.
        $table->id();
        $table->foreignId('user_id');
        $table->foreignId('post_id');
        $table->string('type')->default('like');
        $table->timestamps();
    });

    $schema->create('comment_reports', function (Blueprint $table) {
        // Comment reports support automated moderation thresholds in tests.
        $table->id();
        $table->foreignId('comment_id');
        $table->foreignId('user_id');
        $table->text('reason')->nullable();
        $table->timestamps();
    });

    $schema->create('shares', function (Blueprint $table) {
        // Share records feed analytics for redistribution metrics.
        $table->id();
        $table->foreignId('user_id');
        $table->foreignId('post_id');
        $table->timestamps();
    });

    $schema->create('activity_logs', function (Blueprint $table) {
        // Activity logs capture security events and moderation outcomes during tests.
        $table->id();
        $table->foreignId('user_id');
        $table->string('action');
        $table->string('description');
        // Include the extended metadata columns used by login auditing helpers.
        $table->string('severity')->default('info');
        $table->string('ip_address')->nullable();
        $table->text('user_agent')->nullable();
        $table->json('metadata')->nullable();
        $table->timestamps();
    });

    $schema->create('notifications', function (Blueprint $table) {
        // Standard notification table mirrors Laravel's default schema for database channel delivery.
        $table->uuid('id')->primary();
        $table->string('type');
        $table->morphs('notifiable');
        $table->text('data');
        $table->timestamp('read_at')->nullable();
        $table->timestamps();
    });

    $schema->create('friendships', function (Blueprint $table) {
        // Friendships enable analytics to compute social graph statistics.
        $table->id();
        $table->foreignId('sender_id');
        $table->foreignId('recipient_id');
        $table->string('status')->default('pending');
        $table->timestamp('accepted_at')->nullable();
        $table->timestamps();
    });

    $schema->create('blocks', function (Blueprint $table) {
        // Block relationships allow tests to mirror the UI toggle state.
        $table->id();
        $table->foreignId('blocker_id');
        $table->foreignId('blocked_id');
        $table->timestamps();
    });

    $schema->create('messages', function (Blueprint $table) {
        // Messages back the direct messaging component and its read receipts.
        $table->id();
        $table->foreignId('sender_id');
        $table->foreignId('receiver_id');
        $table->text('content');
        $table->boolean('read')->default(false);
        $table->timestamps();
    });

    $schema->create('follows', function (Blueprint $table) {
        // Follow relationships supply follower counts for analytics growth tracking.
        $table->id();
        $table->foreignId('follower_id');
        $table->foreignId('followed_id');
        $table->boolean('notify')->default(true);
        $table->timestamps();
    });

    $schema->create('pets', function (Blueprint $table) {
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

    $schema->create('pet_friendships', function (Blueprint $table) {
        // Pet friendships mirror the bidirectional relationship layer for animals.
        $table->id();
        $table->foreignId('pet_id');
        $table->foreignId('friend_pet_id');
        $table->string('category')->nullable();
        $table->string('status')->default('accepted');
        $table->timestamp('accepted_at')->nullable();
        $table->timestamps();
    });

    $schema->create('account_recoveries', function (Blueprint $table) {
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

    $schema->create('reports', function (Blueprint $table) {
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
}

beforeEach(function () {
    prepareTestDatabase();
});
