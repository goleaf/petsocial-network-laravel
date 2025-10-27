<?php

use App\Http\Livewire\Content\ShareButton;
use App\Models\Post;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

/**
 * Livewire rendering assertions ensure the component view reacts to state transitions.
 */
it('updates the rendered labels and badge when toggling shares', function (): void {
    // Create a single user to act as both author and viewer, preventing notification persistence in the test database.
    $user = User::factory()->create();
    $post = Post::query()->create([
        'user_id' => $user->id,
        'content' => 'Morning fetch session by the lake.',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // Authenticate the viewer so the Livewire component can read auth()->user().
    actingAs($user);

    // Boot the component with a fresh state and confirm the default Share label is rendered.
    $component = Livewire::test(ShareButton::class, ['postId' => $post->id]);
    $component->assertSee(__('share.share'));
    $component->assertDontSee(__('share.unshare'));

    // Call the share action to flip the state and trigger the badge rendering branch.
    $component->call('share')->assertSet('isShared', true);
    $component->assertSee(__('share.unshare'));

    // Inspect the rendered HTML to ensure the share counter badge is visible with the correct styling.
    expect($component->html())->toContain('span class="ml-1.5 px-2 py-0.5 bg-blue-100 text-blue-800 text-xs font-medium rounded-full">1</span>');
});
