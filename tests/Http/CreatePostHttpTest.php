<?php

use App\Http\Livewire\Content\CreatePost;
use App\Models\User;
use Illuminate\Support\Facades\Route;

/**
 * HTTP layer tests ensuring the CreatePost component can be served via routes.
 */
it('renders the create post composer when routed through HTTP', function () {
    // Authenticate a user so the Livewire component can resolve relationships during render.
    $author = User::factory()->create();
    $this->actingAs($author);

    // Register a temporary route pointing to the component to emulate Filament-style embedding.
    Route::middleware('web')->get('/testing/create-post', CreatePost::class);

    // Dispatch the GET request and confirm the component view renders successfully.
    $this->withoutVite();
    $response = $this->get('/testing/create-post');

    $response->assertOk();
});
