<?php

use App\Models\Comment;
use App\Models\CommentReport;
use App\Models\Post;
use App\Models\PostReport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function (): void {
    // Register the comment reports relationship so the admin component can eager load reports during tests.
    Comment::resolveRelationUsing('reports', function (Comment $comment) {
        return $comment->hasMany(CommentReport::class, 'comment_id');
    });
});

it('displays managed users and reported content for administrators', function (): void {
    // Create an administrator and authenticate them to access the management dashboard.
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);
    actingAs($admin);

    // Create another member to be listed within the management table.
    $member = User::factory()->create([
        'role' => 'user',
        'name' => 'Community Member',
        'email' => 'member@example.com',
    ]);

    // Seed a reported post so the moderation summary renders meaningful data.
    $post = Post::create([
        'user_id' => $member->id,
        'content' => 'Flagged post content',
    ]);
    $reporter = User::factory()->create();
    PostReport::create([
        'user_id' => $reporter->id,
        'post_id' => $post->id,
        'reason' => 'Inappropriate language',
    ]);

    // Seed a reported comment to ensure the component surfaces comment-level moderation data as well.
    $comment = Comment::create([
        'commentable_type' => Post::class,
        'commentable_id' => $post->id,
        'user_id' => $member->id,
        'content' => 'Flagged comment body',
    ]);
    CommentReport::create([
        'user_id' => $reporter->id,
        'comment_id' => $comment->id,
        'reason' => 'Spam reply',
    ]);

    // Visit the admin manage users route and assert the reported content and member appear on screen.
    $response = get(route('admin.users'));

    $response->assertOk();
    $response->assertSeeText('Community Member');
    $response->assertSeeText('Flagged post content');
    $response->assertSeeText('Flagged comment body');
});
