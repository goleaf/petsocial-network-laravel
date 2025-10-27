<?php

use App\Http\Livewire\Content\ShareButton;
use App\Models\Post;
use App\Models\Share;
use App\Models\User;

use function Pest\Laravel\actingAs;

/**
 * Unit level coverage that invokes the component methods directly without the Livewire test harness.
 */
it('initialises state during mount when the viewer already shared the post', function (): void {
    // Prepare a post author, an authenticated viewer, and a pre-existing share record.
    $author = User::factory()->create();
    $viewer = User::factory()->create();
    $post = Post::query()->create([
        'user_id' => $author->id,
        'content' => 'Sunset stroll through the park.',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    Share::query()->create([
        'user_id' => $viewer->id,
        'post_id' => $post->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // Authenticate the viewer so the component can resolve auth()->user() calls.
    actingAs($viewer);

    // Manually instantiate the Livewire component and execute the mount lifecycle hook.
    $component = app(ShareButton::class);
    $component->mount($post->id);

    // Validate the mount lifecycle captured the expected state from the database.
    expect($component->postId)->toBe($post->id);
    expect($component->isShared)->toBeTrue();
    expect($component->shareCount)->toBe(1);
});

/**
 * Ensure the share method mutates state correctly when invoked directly.
 */
it('creates and deletes share records through the public share method', function (): void {
    // Provision the post context with a single authenticated author/viewer to avoid notification persistence.
    $user = User::factory()->create();
    $post = Post::query()->create([
        'user_id' => $user->id,
        'content' => 'Puppy training recap.',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    actingAs($user);

    // Drive the component lifecycle manually to call share and then unshare.
    $component = app(ShareButton::class);
    $component->mount($post->id);
    $component->share();

    // Verify the component reflected the new share state and the record exists.
    expect($component->isShared)->toBeTrue();
    expect($component->shareCount)->toBe(1);
    expect(Share::query()->where('post_id', $post->id)->where('user_id', $user->id)->exists())->toBeTrue();

    // Invoke share again to traverse the deletion branch and confirm cleanup.
    $component->share();

    expect($component->isShared)->toBeFalse();
    expect($component->shareCount)->toBe(0);
    expect(Share::query()->where('post_id', $post->id)->where('user_id', $user->id)->exists())->toBeFalse();
});
