<?php

use App\Http\Livewire\Admin\Analytics;
use App\Models\FriendRequest;
use App\Models\Post;
use App\Models\Reaction;
use App\Models\Share;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Unit-level coverage for the aggregate loader keeps the calculations honest.
 */
it('recalculates metrics whenever loadAnalytics is invoked', function () {
    // Provision the SQLite schema so loadAnalytics can operate on the seeded datasets.
    prepareTestDatabase();

    // Seed the minimal data set for the first metrics snapshot.
    $admin = User::factory()->create(['role' => 'admin']);
    $participant = User::factory()->create();

    Post::create([
        'content' => 'Initial weekly recap',
        'user_id' => $participant->id,
    ]);

    // Resolve the component directly so we can call the method without a Livewire harness.
    $component = app(Analytics::class);
    $component->loadAnalytics();

    expect($component->userCount)->toBe(2)
        ->and($component->postCount)->toBe(1)
        ->and($component->reactionCount)->toBe(0)
        ->and($component->friendCount)->toBe(0);

    // Introduce additional interactions and ensure the recalculation reflects them.
    DB::table('comments')->insert([
        'user_id' => $admin->id,
        'post_id' => Post::first()->id,
        'content' => 'Thanks for aggregating the stats!',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    Reaction::create([
        'user_id' => $admin->id,
        'post_id' => Post::first()->id,
        'type' => 'celebrate',
    ]);

    Share::create([
        'user_id' => $admin->id,
        'post_id' => Post::first()->id,
    ]);

    FriendRequest::create([
        'sender_id' => $participant->id,
        'receiver_id' => $admin->id,
        'status' => 'accepted',
    ]);

    FriendRequest::create([
        'sender_id' => $admin->id,
        'receiver_id' => $participant->id,
        'status' => 'accepted',
    ]);

    $component->loadAnalytics();

    expect($component->commentCount)->toBe(1)
        ->and($component->reactionCount)->toBe(1)
        ->and($component->shareCount)->toBe(1)
        ->and($component->friendCount)->toBe(1);
});

it('renders the analytics blade view for administrators', function () {
    // Provision the table structure before hydrating metrics for the Blade template.
    prepareTestDatabase();

    // Resolve the Livewire component through the service container for a realistic instance.
    $component = app(Analytics::class);

    // Hydrate the metrics so the Blade view receives the same props exposed in production.
    $component->loadAnalytics();

    $view = $component->render();

    // The rendered view should map to the dedicated admin analytics Blade template.
    expect(view()->exists('livewire.admin.analytics'))->toBeTrue()
        ->and($view->name())->toBe('livewire.admin.analytics');
});
