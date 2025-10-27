<?php

use App\Http\Livewire\Content\CreatePost;
use App\Models\User;

it('resolves mentioned usernames into user models', function () {
    // Seed the mention target so the parser has a concrete record to discover.
    $mentioned = User::factory()->create(['username' => 'buddy']);

    // Instantiate the Livewire component directly for an isolated unit-level assertion.
    $component = app(CreatePost::class);

    // Expose the protected parseMentions helper via reflection to assert its pure behaviour.
    $parser = new \ReflectionMethod(CreatePost::class, 'parseMentions');
    $parser->setAccessible(true);

    // Provide content with a valid mention and an unrelated token to ensure filtering works correctly.
    $results = $parser->invoke($component, 'Chatting with @buddy while ignoring @stranger.');

    // Confirm only the registered user was resolved and the correct model instance was returned.
    expect($results)->toHaveCount(1)
        ->and($results->first()->is($mentioned))->toBeTrue();
});
