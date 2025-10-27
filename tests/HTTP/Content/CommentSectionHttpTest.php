<?php

use App\Http\Livewire\Content\CommentSection;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Route;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

it('responds with the Livewire markup when the comment section is routed over HTTP', function () {
    // Establish a signed-in user and backing post so the Livewire route can resolve cleanly.
    $user = User::factory()->create(['name' => 'HttpUser']);
    $post = Post::create([
        'user_id' => $user->id,
        'content' => 'Route level post',
    ]);

    actingAs($user);

    // Register a temporary route that points directly at the Livewire component as Filament would.
    Route::middleware('web')->get('/testing/posts/{post}/comments', CommentSection::class)->name('testing.comments');

    // Execute a standard HTTP request to confirm the component renders without Livewire test helpers.
    $response = get('/testing/posts/'.$post->id.'/comments');

    $response->assertOk();
    $response->assertSee('<textarea', false);
    $response->assertSee('wire:submit.prevent="save"', false);
});
