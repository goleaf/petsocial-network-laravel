<?php

use App\Http\Livewire\Content\CommentSection;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

beforeEach(function () {
    // Reinitialize the test database so component hydration reads from a predictable dataset.
    prepareTestDatabase();
});

it('parses mention tokens into concrete user models for notification routing', function () {
    // Set up a post so the component can execute its mount lifecycle as expected.
    $postOwner = User::factory()->create(['name' => 'OwnerZero']);
    $post = Post::create([
        'user_id' => $postOwner->id,
        'content' => 'Owner announcement',
    ]);

    // Instantiate the component manually so we can exercise the protected helper.
    $component = app(CommentSection::class);
    $component->mount($post->id);

    // Create users with machine-friendly names that match the regex expectations.
    $target = User::factory()->create(['name' => 'BuddyOne']);
    User::factory()->create(['name' => 'IgnoredUser']);

    // Call the protected parseMentions helper via reflection to inspect its output.
    $method = new ReflectionMethod(CommentSection::class, 'parseMentions');
    $method->setAccessible(true);

    $mentionedUsers = $method->invoke($component, 'Ping @BuddyOne and @MissingUser');

    // The helper should return only the persisted user while ignoring unknown handles.
    expect($mentionedUsers)->toHaveCount(1);
    expect($mentionedUsers->first()->is($target))->toBeTrue();
});

it('renders the expected blade view and exposes a paginator of top-level comments', function () {
    // Create a post and associated comment to confirm hydration of the component state.
    $owner = User::factory()->create(['name' => 'BladeOwner']);
    $post = Post::create([
        'user_id' => $owner->id,
        'content' => 'Post requiring blade rendering checks',
    ]);
    Comment::create([
        'user_id' => $owner->id,
        'post_id' => $post->id,
        'content' => 'A visible top-level comment',
    ]);

    // Instantiate the component just like Livewire would during a request lifecycle.
    $component = app(CommentSection::class);
    $component->mount($post->id);

    // Execute the render cycle to retrieve the blade view that powers the UI.
    $view = $component->render();

    // Ensure the component resolved the correct blade template for the comment section UI.
    expect($view->name())->toBe('livewire.comment-section');

    // Confirm the view data contains a paginator instance with the hydrated comment data.
    $comments = $view->getData()['comments'];
    expect($comments)->toBeInstanceOf(LengthAwarePaginator::class);
    expect($comments->total())->toBe(1);
});
