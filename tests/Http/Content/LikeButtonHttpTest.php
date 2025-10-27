<?php

use App\Http\Livewire\Content\LikeButton;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use function Pest\Laravel\actingAs;

it('returns like button markup when requested through an http endpoint', function (): void {
    /**
     * Seed a post and register a throwaway route used exclusively for this HTTP assertion.
     */
    $user = User::factory()->create();
    $post = Post::query()->create([
        'user_id' => $user->id,
        'content' => 'Route powered Livewire rendering check.',
    ]);
    Route::middleware('web')->get('/testing-like-button', function () use ($post) {
        // Mount the Livewire component using the testing helper to mirror a controller-driven response.
        return Livewire::test(LikeButton::class, ['postId' => $post->id])->html();
    });

    /**
     * Authenticate the request and verify the rendered response contains the like call-to-action.
     */
    actingAs($user);
    $response = $this->get('/testing-like-button');
    $response->assertOk();
    $response->assertSee('Like');
});
