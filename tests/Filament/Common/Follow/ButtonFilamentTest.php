<?php

use App\Http\Livewire\Common\Follow\Button;
use Livewire\Livewire;
use Tests\Support\FollowButtonTestHelper;
use Tests\Support\FollowButtonUserStub;

/**
 * Validate that the follow button markup stays compatible with Filament panel embeddings.
 */
afterEach(function (): void {
    // Release the Mockery alias to keep isolation between Filament oriented tests.
    \Mockery::close();
});

it('exposes predictable utility classes suited for Filament shells', function (): void {
    // Prepare a basic follow relationship so the component renders the follow action state.
    $entity = new FollowButtonUserStub(9009, false, false);
    $target = new FollowButtonUserStub(1010);

    // Provide the stubbed models through the mocked User::findOrFail calls.
    FollowButtonTestHelper::mockUsers($entity, $target);

    // Render the component and validate the layout classes align with Filament utility expectations.
    Livewire::test(Button::class, [
        'entityType' => 'user',
        'entityId' => $entity->id,
        'targetId' => $target->id,
    ])->assertSeeHtml('class="flex flex-col items-end"')
        ->assertSeeHtml('class="inline-flex items-center px-3 py-1');
});
