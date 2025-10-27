<?php

use App\Http\Livewire\Content\ReportPost;
use App\Models\Post;
use App\Models\User;
use Livewire\Livewire;

// Validate the Livewire component enforces the reason rules before reporting.
it('requires a reason and limits its length when reporting a post', function (): void {
    // Prepare an authenticated user and post to interact with the component.
    $author = User::factory()->create();
    $reporter = User::factory()->create();
    $post = Post::create([
        'user_id' => $author->id,
        'content' => 'A suspicious looking advertisement.',
    ]);

    $component = Livewire::actingAs($reporter)->test(ReportPost::class, [
        'postId' => $post->id,
    ]);

    // Ensure the component renders the correct Blade template so UI assertions stay valid.
    $component->assertViewIs('livewire.report-post');

    // Empty submissions should hit the required validation rule.
    $component->call('report')
        ->assertHasErrors(['reason' => 'required']);

    // Overly long messages should trigger the max length constraint.
    $component->set('reason', str_repeat('a', 260));
    $component->call('report')
        ->assertHasErrors(['reason' => 'max']);
});
