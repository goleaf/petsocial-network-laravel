<?php

use App\Models\Comment;
use App\Models\CommentReport;
use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;
use Tests\TestCase;

uses(TestCase::class)->in('Feature', 'Livewire', 'Http', 'Filament', 'Unit');

uses()->beforeEach(function () {
    Config::set('database.default', 'sqlite');
    Config::set('database.connections.sqlite', [
        'driver' => 'sqlite',
        'database' => ':memory:',
        'prefix' => '',
        'foreign_key_constraints' => true,
    ]);

    Schema::dropIfExists('reports');
    Schema::dropIfExists('pet_activities');
    Schema::dropIfExists('user_activities');
    Schema::dropIfExists('messages');
    Schema::dropIfExists('friend_requests');
    Schema::dropIfExists('post_tag');
    Schema::dropIfExists('tags');
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
        $table->string('commentable_type')->nullable();
        $table->unsignedBigInteger('commentable_id')->nullable();
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

    Schema::create('friend_requests', function (Blueprint $table) {
        // Friend requests power mutual connection metrics for the admin dashboard.
        $table->id();
        $table->foreignId('sender_id');
        $table->foreignId('receiver_id');
        $table->string('status')->default('pending');
        $table->string('category')->nullable();
        $table->timestamps();
    });

    Schema::create('messages', function (Blueprint $table) {
        // Messages table backs the private messaging counters surfaced on the dashboard.
        $table->id();
        $table->foreignId('sender_id');
        $table->foreignId('receiver_id');
        $table->text('content');
        $table->boolean('read')->default(false);
        $table->timestamps();
    });

    Schema::create('user_activities', function (Blueprint $table) {
        // User activity feed supplies analytics for daily active user metrics.
        $table->id();
        $table->foreignId('user_id');
        $table->string('type');
        $table->text('description')->nullable();
        $table->json('data')->nullable();
        $table->string('actor_type')->nullable();
        $table->unsignedBigInteger('actor_id')->nullable();
        $table->string('target_type')->nullable();
        $table->unsignedBigInteger('target_id')->nullable();
        $table->boolean('read')->default(false);
        $table->timestamps();
    });

    Schema::create('pet_activities', function (Blueprint $table) {
        // Pet activity history fuels the "top pets" leaderboard inside the admin view.
        $table->id();
        $table->foreignId('pet_id');
        $table->string('type');
        $table->text('description')->nullable();
        $table->string('location')->nullable();
        $table->timestamp('happened_at')->nullable();
        $table->string('image')->nullable();
        $table->boolean('is_public')->default(true);
        $table->json('data')->nullable();
        $table->string('actor_type')->nullable();
        $table->unsignedBigInteger('actor_id')->nullable();
        $table->string('target_type')->nullable();
        $table->unsignedBigInteger('target_id')->nullable();
        $table->boolean('read')->default(false);
        $table->timestamps();
    });

    Schema::create('tags', function (Blueprint $table) {
        // Tags table is required for layout components referenced by the admin dashboard.
        $table->id();
        $table->string('name');
        $table->timestamps();
    });

    Schema::create('post_tag', function (Blueprint $table) {
        // Pivot table enabling tag lookups for trending components embedded in the layout.
        $table->unsignedBigInteger('post_id');
        $table->unsignedBigInteger('tag_id');
    });

    // Ensure entity initialisation hooks respect aggregate queries that omit primary keys.
    User::flushEventListeners();
    User::created(static function (User $model): void {
        $model->initializeEntity('user', $model->id);
    });
    User::retrieved(static function (User $model): void {
        if ($model->id === null) {
            return;
        }

        $model->initializeEntity('user', $model->id);
    });

    if (! Route::has('admin.reports') || ! Route::has('admin.settings')) {
        Route::prefix('admin')->name('admin.')->group(function () {
            if (! Route::has('admin.reports')) {
                Route::get('/reports', static fn () => 'reports')->name('reports');
            }

            if (! Route::has('admin.settings')) {
                Route::get('/settings', static fn () => 'settings')->name('settings');
            }
        });
    }

    if (! method_exists(Component::class, 'emit')) {
        Component::macro('emit', function (string $event, ...$payload): void {
            $this->dispatch($event, ...$payload);
        });
    }

    // Register lightweight morph relationships required by the admin dashboard aggregates.
    User::resolveRelationUsing('reports', static fn (User $model) => $model->morphMany(Report::class, 'reportable'));
    Comment::resolveRelationUsing('reports', static fn (Comment $model) => $model->hasMany(CommentReport::class, 'comment_id'));
})->in('Feature', 'Livewire', 'Http', 'Filament', 'Unit');
