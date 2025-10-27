<?php

use App\Http\Livewire\Content\CreatePost;
use App\Models\User;
use Livewire\Livewire;

/**
 * Livewire interaction tests for the CreatePost component UI behaviours.
 */
it('surfaces mention suggestions and inserts the selected username', function () {
    // Create the author and potential mention targets that should appear in the dropdown.
    $author = User::factory()->create();
    User::factory()->create(['username' => 'buddy', 'name' => 'Buddy Barkley']);
    User::factory()->create(['username' => 'buddylover', 'name' => 'Buddy Lover']);

    $this->actingAs($author);

    // Type a mention trigger so the component searches for matching users.
    $component = Livewire::test(CreatePost::class)
        ->set('content', 'Hanging out with @bud');

    // Ensure the mention query and dropdown state reflect the typed handle fragment.
    $component->assertSet('mentionQuery', 'bud')
        ->assertSet('showMentionDropdown', true);

    // Insert the selected username into the composer and confirm the dropdown closes.
    $component->call('selectMention', 'buddy')
        ->assertSet('content', 'Hanging out with @buddy ')
        ->assertSet('showMentionDropdown', false);
});
