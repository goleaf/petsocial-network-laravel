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
    // Ensure the component can query reported comments when preparing select options for administrative tools.
    Comment::resolveRelationUsing('reports', function (Comment $comment) {
        return $comment->hasMany(CommentReport::class, 'comment_id');
    });
});

it('exposes labelled role options suitable for Filament form selects', function (): void {
    // Store the original access configuration so it can be restored after the scenario.
    $originalRoles = config('access.roles');

    // Override the role labels to simulate Filament select options needing readable names.
    config(['access.roles' => [
        'admin' => [
            'label' => 'Administrator',
            'permissions' => ['*'],
        ],
        'moderator' => [
            'label' => 'Community Moderator',
            'permissions' => ['moderation.*'],
        ],
        'user' => [
            'label' => 'Member',
            'permissions' => ['profile.update'],
        ],
    ]]);

    // Authenticate as an administrator to mount the component successfully.
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);
    actingAs($admin);

    // Mount the component through Livewire so the mount lifecycle hydrates the roleOptions array.
    $component = Livewire::test(ManageUsers::class);

    // Assert the component exposes a label map ideal for Filament select fields.
    expect($component->get('roleOptions'))->toBe([
        'admin' => 'Administrator',
        'moderator' => 'Community Moderator',
        'user' => 'Member',
    ]);

    // Restore the original role configuration to prevent cross-test leakage.
    config(['access.roles' => $originalRoles]);
});
