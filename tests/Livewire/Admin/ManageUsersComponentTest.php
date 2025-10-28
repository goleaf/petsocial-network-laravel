<?php

use App\Http\Livewire\Admin\ManageUsers;
use App\Models\Comment;
use App\Models\CommentReport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    // Provide the reports relationship so Livewire can pull comment data without hitting missing relation exceptions.
    Comment::resolveRelationUsing('reports', function (Comment $comment) {
        return $comment->hasMany(CommentReport::class, 'comment_id');
    });
});

it('updates user information through the Livewire admin workflow', function (): void {
    // Authenticate as an administrator to access the component lifecycle.
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);
    actingAs($admin);

    // Create a member whose profile will be edited through the Livewire actions.
    $member = User::factory()->create([
        'role' => 'user',
        'name' => 'Original Name',
        'email' => 'original@example.com',
    ]);

    // Drive the edit workflow, update the fields, and persist the changes via the component.
    Livewire::test(ManageUsers::class)
        ->call('editUser', $member->id)
        ->set('editName', 'Updated Name')
        ->set('editEmail', 'updated@example.com')
        ->set('editRole', 'moderator')
        ->call('updateUser')
        ->assertHasNoErrors()
        ->assertSet('editingUserId', null);

    // Confirm the member model reflects the requested updates and new role assignment.
    expect($member->fresh()->only(['name', 'email', 'role']))->toMatchArray([
        'name' => 'Updated Name',
        'email' => 'updated@example.com',
        'role' => 'moderator',
    ]);
});
