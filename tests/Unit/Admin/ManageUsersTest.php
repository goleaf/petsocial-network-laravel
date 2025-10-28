<?php

use App\Http\Livewire\Admin\ManageUsers;
use App\Models\Comment;
use App\Models\CommentReport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    // Wire up the dynamic reports relationship to prevent query errors when loadData runs.
    Comment::resolveRelationUsing('reports', function (Comment $comment) {
        return $comment->hasMany(CommentReport::class, 'comment_id');
    });
});

it('suspends a member and resets suspension form state', function (): void {
    // Authenticate as an administrator so the component can refresh its datasets during the workflow.
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);
    auth()->login($admin);

    // Create a member that will be suspended through the component logic.
    $member = User::factory()->create([
        'role' => 'user',
    ]);

    // Instantiate the component directly to exercise the confirmSuspend handler in isolation.
    $component = new ManageUsers();
    $component->suspendUserId = $member->id;
    $component->suspendDays = 5;
    $component->suspendReason = 'Manual moderation hold';

    // Trigger the confirmation routine which validates input, applies the suspension, and refreshes data.
    $component->confirmSuspend();

    // Ensure the member is suspended and the temporary form fields were cleared.
    expect($member->fresh()->isSuspended())->toBeTrue();
    expect($component->suspendUserId)->toBeNull();
    expect($component->suspendDays)->toBeNull();
    expect($component->suspendReason)->toBeNull();
});
