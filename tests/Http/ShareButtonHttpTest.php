<?php

use App\Http\Livewire\Content\ShareButton;
use App\Models\Post;
use App\Models\Share;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\postJson;

/**
 * HTTP level coverage ensures the component logic behaves correctly when invoked via a route closure.
 */
it('processes share toggles through an ad-hoc http endpoint', function (): void {
    // Provision the core models used by the share button, reusing a single user to avoid notification persistence.
    $user = User::factory()->create();
    $post = Post::query()->create([
        'user_id' => $user->id,
        'content' => 'Trail run highlights for the pack.',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // Authenticate the viewer to mimic a web request initiated by a logged-in member.
    actingAs($user);

    // Register a lightweight testing route that instantiates the component and proxies to the share method.
    Route::post('/testing/share-button/toggle', function (Request $request) {
        $component = app(ShareButton::class);
        $component->mount($request->integer('post_id'));
        $component->share();

        return response()->json([
            'isShared' => $component->isShared,
            'shareCount' => $component->shareCount,
        ]);
    });

    // Issue the first HTTP request to create the share record.
    $response = postJson('/testing/share-button/toggle', ['post_id' => $post->id]);
    $response->assertOk()->assertJson([
        'isShared' => true,
        'shareCount' => 1,
    ]);

    // Confirm the database persisted the share entry for the viewer.
    expect(
        Share::query()->where('post_id', $post->id)->where('user_id', $user->id)->exists()
    )->toBeTrue();

    // Send a second request to traverse the unshare branch via HTTP.
    $secondResponse = postJson('/testing/share-button/toggle', ['post_id' => $post->id]);
    $secondResponse->assertOk()->assertJson([
        'isShared' => false,
        'shareCount' => 0,
    ]);

    // Ensure the record has been removed once the unshare completes.
    expect(
        Share::query()->where('post_id', $post->id)->where('user_id', $user->id)->exists()
    )->toBeFalse();
});
