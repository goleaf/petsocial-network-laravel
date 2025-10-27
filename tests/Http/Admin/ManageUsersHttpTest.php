<?php

use App\Http\Livewire\Admin\ManageUsers;
use App\Models\Comment;
use App\Models\CommentReport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function (): void {
    // Register the comment reports relation so any incidental component boot cycles remain stable.
    Comment::resolveRelationUsing('reports', function (Comment $comment) {
        return $comment->hasMany(CommentReport::class, 'comment_id');
    });
});

it('redirects non administrators away from the admin manage users page', function (): void {
    // Authenticate as a regular member lacking the admin access permission.
    $member = User::factory()->create([
        'role' => 'user',
    ]);
    actingAs($member);

    // Attempt to access the admin manage users route and confirm the middleware redirects the user.
    $response = get(route('admin.users'));

    $response->assertRedirect('/');
});

it('allows administrators to load the manage users Livewire interface', function (): void {
    // Authenticate as an administrator to satisfy the middleware requirements.
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);
    actingAs($admin);

    // Request the manage users page and confirm the Livewire component and headings render as expected.
    $response = get(route('admin.users'));

    $response->assertOk();
    $response->assertSeeLivewire(ManageUsers::class);
    $response->assertSeeText(__('admin.manage_users'));
});
