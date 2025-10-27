<?php

use App\Http\Livewire\Content\CreatePost;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Tests\Support\PostDraft;
use Tests\Support\PostImage;
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

    // Register lightweight relations that mirror the production associations used by CreatePost.
    User::resolveRelationUsing('postDrafts', fn ($user) => $user->hasMany(PostDraft::class, 'user_id'));
    Post::resolveRelationUsing('images', fn ($post) => $post->hasMany(PostImage::class, 'post_id'));
    CreatePost::macro('emit', function (string $event, ...$payload) {
        // No-op placeholder allowing legacy emit() calls during Livewire v3 tests.
        return null;
    });

    Schema::dropIfExists('reports');
    Schema::dropIfExists('activity_logs');
    Schema::dropIfExists('post_reports');
    Schema::dropIfExists('post_images');
    Schema::dropIfExists('post_tag');
    Schema::dropIfExists('tags');
    Schema::dropIfExists('post_drafts');
    Schema::dropIfExists('posts');
    Schema::dropIfExists('comments');
    Schema::dropIfExists('comment_reports');
    Schema::dropIfExists('reactions');
    Schema::dropIfExists('shares');
    Schema::dropIfExists('pet_friendships');
    Schema::dropIfExists('pets');
    Schema::dropIfExists('friendships');
    Schema::dropIfExists('account_recoveries');
    Schema::dropIfExists('notifications');
    Schema::dropIfExists('users');

    Schema::create('users', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('email')->unique();
        // Username column powers mention lookups in CreatePost component tests.
        $table->string('username')->unique();
        $table->timestamp('email_verified_at')->nullable();
        $table->string('password');
        $table->rememberToken();
        $table->string('role')->default('user');
        // Profile photo mirrors production schema so mention results can surface avatars.
        $table->string('profile_photo_path')->nullable();
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
        // Optional pet association enables ownership validation scenarios.
        $table->foreignId('pet_id')->nullable();
        $table->text('content');
        // Visibility column allows feature tests to verify the saved setting.
        $table->string('visibility')->default('public');
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
        // Additional metadata columns align with ActivityLog::record usage.
        $table->string('severity')->default('info');
        $table->string('ip_address')->nullable();
        $table->string('user_agent')->nullable();
        $table->json('metadata')->nullable();
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

    Schema::create('tags', function (Blueprint $table) {
        // Tag catalogue is required for autocomplete and association tests.
        $table->id();
        $table->string('name')->unique();
        $table->timestamps();
    });

    Schema::create('post_tag', function (Blueprint $table) {
        // Pivot table linking posts and tags for categorisation scenarios.
        $table->id();
        $table->foreignId('post_id');
        $table->foreignId('tag_id');
    });

    Schema::create('post_images', function (Blueprint $table) {
        // Storage metadata for uploaded post images.
        $table->id();
        $table->foreignId('post_id');
        $table->string('path');
        $table->string('name');
        $table->unsignedBigInteger('size');
        $table->string('mime_type');
        $table->timestamps();
    });

    Schema::create('post_drafts', function (Blueprint $table) {
        // Draft persistence keeps unsaved post progress between sessions.
        $table->string('id')->primary();
        $table->foreignId('user_id');
        $table->text('content');
        $table->string('tags')->nullable();
        $table->foreignId('pet_id')->nullable();
        $table->string('visibility')->default('public');
        $table->timestamps();
    });

    Schema::create('notifications', function (Blueprint $table) {
        // Database notification channel used by ActivityNotification.
        $table->uuid('id')->primary();
        $table->string('type');
        $table->morphs('notifiable');
        $table->json('data');
        $table->timestamp('read_at')->nullable();
        $table->timestamps();
    });
})->in('Feature', 'Unit', 'Livewire', 'Filament', 'Http');
