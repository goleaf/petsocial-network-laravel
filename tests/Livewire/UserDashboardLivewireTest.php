<?php

use App\Http\Livewire\UserDashboard;
use App\Models\Profile;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

/**
 * Livewire behavioural coverage for the UserDashboard component.
 */
it('refreshes the feed when post lifecycle events are dispatched', function () {
    // Stabilise timestamps so feed ordering remains predictable during assertions.
    Carbon::setTestNow(Carbon::parse('2025-05-02 09:00:00'));

    // Create the authenticated member and provision a profile so avatar lookups remain safe.
    $member = User::factory()->create(['name' => 'Casey']);
    Profile::create(['user_id' => $member->id]);

    actingAs($member);

    // Seed an initial post so the component renders real content immediately.
    DB::table('posts')->insert([
        'user_id' => $member->id,
        'content' => 'First morning update',
        'posts_visibility' => 'public',
        'created_at' => now()->subMinutes(5),
        'updated_at' => now()->subMinutes(5),
    ]);

    // Mount the Livewire component and verify the initial feed payload is present.
    $component = Livewire::test(UserDashboard::class)
        ->assertSee('First morning update');

    // Insert a new post to mimic another publish action that should appear after a reload.
    DB::table('posts')->insert([
        'user_id' => $member->id,
        'content' => 'Second morning update',
        'posts_visibility' => 'public',
        'created_at' => now()->subMinutes(1),
        'updated_at' => now()->subMinutes(1),
    ]);

    // Dispatch the same event the component listens for so it reloads the paginated collection.
    $component->dispatch('postCreated')
        ->assertSee('Second morning update');

    // Release the frozen clock to avoid influencing unrelated tests.
    Carbon::setTestNow();
});
