<?php

use App\Http\Livewire\Admin\Analytics;
use App\Models\Post;
use App\Models\User;
use Livewire\Livewire;

/**
 * Livewire-focused assertions ensure the component hydrates and exposes props correctly.
 */
it('orders the top users collection by post volume', function () {
    // Ensure the ephemeral SQLite schema exists before creating any records for the leaderboard.
    prepareTestDatabase();

    // Authenticate as an administrator to mimic the middleware guarding the component.
    $admin = User::factory()->create(['role' => 'admin']);
    $leader = User::factory()->create(['name' => 'Trailblazer']);
    $runnerUp = User::factory()->create(['name' => 'Contender']);

    // Create a heavier posting cadence for the leading author.
    foreach (range(1, 3) as $index) {
        Post::create([
            'content' => "Leader spotlight {$index}",
            'user_id' => $leader->id,
        ]);
    }

    Post::create([
        'content' => 'Runner up training notes',
        'user_id' => $runnerUp->id,
    ]);

    $this->actingAs($admin);

    $component = Livewire::test(Analytics::class);

    // Confirm the component resolves the expected Blade view before inspecting the computed props.
    $component->assertViewIs('livewire.admin.analytics');

    // Validate that the Livewire property order reflects the computed post counts.
    $component
        ->assertSet('topUsers.0.id', $leader->id)
        ->assertSet('topUsers.0.posts_count', 3)
        ->assertSet('topUsers.1.posts_count', 1)
        ->assertViewHas('topUsers', function ($users) use ($leader) {
            // The rendered view should receive a collection ranked by posts_count.
            return $users->first()->id === $leader->id && $users->first()->posts_count === 3;
        });
});
