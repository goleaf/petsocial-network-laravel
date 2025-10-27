<?php

use App\Http\Livewire\Common\Follow\FollowList;

it('resets pagination whenever the search term updates', function (): void {
    // Instantiate the component directly to verify the pagination helpers without a full Livewire test harness.
    $component = app(FollowList::class);

    // Move the paginator away from page one so we can confirm the reset behaviour.
    $component->setPage(4);
    expect($component->getPage())->toBe(4);

    // The updatingSearch lifecycle hook should snap the paginator back to its starting position.
    $component->updatingSearch();

    expect($component->getPage())->toBe(1);
});
