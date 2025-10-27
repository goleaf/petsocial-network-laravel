<?php

use App\Http\Livewire\Content\ReportPost;
use App\Models\Post;
use App\Models\PostReport;
use App\Models\User;

use function Pest\Laravel\actingAs;

// Confirm the component detects prior reports during the mount lifecycle hook.
it('sets the reported flag when the authenticated user already filed a report', function (): void {
    // Establish a post with a pre-existing report from the authenticated member.
    $author = User::factory()->create();
    $reporter = User::factory()->create();
    $post = Post::create([
        'user_id' => $author->id,
        'content' => 'Content that has already been reviewed.',
    ]);

    actingAs($reporter);

    PostReport::create([
        'user_id' => $reporter->id,
        'post_id' => $post->id,
        'reason' => 'Previously reported for policy violations.',
    ]);

    // Mount the component directly to ensure the lifecycle hook inspects the database state.
    $component = new ReportPost();
    $component->mount($post->id);

    expect($component->reported)->toBeTrue();

    // Rendering the component directly should resolve the expected Blade view name.
    $view = $component->render();
    expect($view->name())->toBe('livewire.report-post');
});
