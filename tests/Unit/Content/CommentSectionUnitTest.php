<?php

use App\Http\Livewire\Content\CommentSection;
use App\Models\Post;
use App\Models\User;

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
