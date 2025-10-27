<?php

use App\Http\Livewire\Content\CreatePost;
use App\Models\User;
use Livewire\Livewire;

it('suggests mention candidates and replaces the placeholder when selected', function () {
    // Create the authenticated user and the friend to be mentioned in the composer.
    $author = User::factory()->create(['username' => 'authoring']);
    $suggested = User::factory()->create(['username' => 'buddy']);

    // Authenticate before interacting with the Livewire component to satisfy guard checks.
    $this->actingAs($author);

    // Type content that includes a mention trigger and ensure suggestions surface accordingly.
    $component = Livewire::test(CreatePost::class)
        ->set('content', 'Chat with @buddy now')
        ->assertSet('showMentionDropdown', true)
        ->assertSet('mentionQuery', 'buddy')
        ->assertSet('mentionResults.0.username', $suggested->username);

    // Choose the suggestion and confirm the placeholder is replaced with a proper mention token.
    $component->call('selectMention', $suggested->username)
        ->assertSet('content', 'Chat with @'.$suggested->username.'  now')
        ->assertSet('showMentionDropdown', false);
});
