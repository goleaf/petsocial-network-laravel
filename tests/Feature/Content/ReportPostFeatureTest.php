<?php

use App\Http\Livewire\Content\ReportPost;
use App\Models\Post;
use App\Models\PostReport;
use App\Models\User;
use Livewire\Livewire;

// Ensure the ReportPost component can persist a report and reset its state.
it('stores a post report and marks the submission as completed', function (): void {
    // Create an author and a viewer so the component has a post to operate against.
    $author = User::factory()->create();
    $reporter = User::factory()->create();

    $post = Post::create([
        'user_id' => $author->id,
        'content' => 'This post contains disallowed content.',
    ]);

    // Run the Livewire component as the reporting user to simulate the full UI flow.
    $component = Livewire::actingAs($reporter)->test(ReportPost::class, [
        'postId' => $post->id,
    ]);

    // Confirm the component is wired to the expected Blade view before any interaction occurs.
    $component->assertViewIs('livewire.report-post');

    $component->set('reason', 'The post includes spam links.');
    $component->call('report');

    // Confirm the database captured the new report record for the post and user.
    expect(PostReport::where('user_id', $reporter->id)
        ->where('post_id', $post->id)
        ->exists())->toBeTrue();

    // The component should now show the success state and clear the input.
    $component->assertSet('reported', true);
    $component->assertSet('reason', '');
});
