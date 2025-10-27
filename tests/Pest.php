<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

// Register the base TestCase for standard feature tests.
uses(TestCase::class)->in('Feature');

// Extend TestCase coverage to other custom suites that interact with Laravel.
uses(TestCase::class)->in('Http', 'Livewire', 'Filament', 'Unit');

uses()->beforeEach(function () {
    // Ensure a consistent in-memory SQLite database is available for every request heavy suite.
    Config::set('database.default', 'sqlite');
    Config::set('database.connections.sqlite', [
        'driver' => 'sqlite',
        'database' => ':memory:',
        'prefix' => '',
        'foreign_key_constraints' => true,
    ]);

    Schema::dropIfExists('reports');
    Schema::dropIfExists('activity_logs');
    Schema::dropIfExists('post_tag');
    Schema::dropIfExists('tags');
    Schema::dropIfExists('post_reports');
    Schema::dropIfExists('posts');
    Schema::dropIfExists('comments');
    Schema::dropIfExists('comment_reports');
    Schema::dropIfExists('reactions');
    Schema::dropIfExists('shares');
    Schema::dropIfExists('pet_medical_visits');
    Schema::dropIfExists('pet_medical_records');
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

    Schema::create('tags', function (Blueprint $table) {
        // Tag records fuel trending tag components rendered on shared layouts.
        $table->id();
        $table->string('name');
        $table->timestamps();
    });

    Schema::create('post_tag', function (Blueprint $table) {
        // Pivot table tracks tag assignments to posts for popularity counts.
        $table->id();
        $table->foreignId('post_id');
        $table->foreignId('tag_id');
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

    Schema::create('pet_medical_records', function (Blueprint $table) {
        // Medical records capture the long-lived profile metadata for a pet.
        $table->id();
        $table->foreignId('pet_id');
        $table->string('primary_veterinarian')->nullable();
        $table->string('clinic_name')->nullable();
        $table->string('clinic_contact')->nullable();
        $table->string('insurance_provider')->nullable();
        $table->string('insurance_policy_number')->nullable();
        $table->date('last_checkup_at')->nullable();
        $table->text('known_conditions')->nullable();
        $table->text('medications')->nullable();
        $table->text('allergies')->nullable();
        $table->text('vaccination_status')->nullable();
        $table->string('microchip_id')->nullable();
        $table->text('dietary_notes')->nullable();
        $table->text('emergency_plan')->nullable();
        $table->timestamps();
    });

    Schema::create('pet_medical_visits', function (Blueprint $table) {
        // Each visit stores historical veterinary information linked back to the record.
        $table->id();
        $table->foreignId('medical_record_id');
        $table->date('visit_date')->nullable();
        $table->string('veterinarian')->nullable();
        $table->string('reason')->nullable();
        $table->string('diagnosis')->nullable();
        $table->text('treatment')->nullable();
        $table->text('medications_prescribed')->nullable();
        $table->date('follow_up_date')->nullable();
        $table->text('notes')->nullable();
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
})->in('Feature', 'Http', 'Livewire');
