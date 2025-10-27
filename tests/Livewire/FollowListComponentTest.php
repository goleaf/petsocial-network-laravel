<?php

use App\Http\Livewire\Common\Follow\FollowList;
use App\Models\User;
use Livewire\Livewire;

it('filters followers through the Livewire component in real time', function (): void {
    // Initialize the SQLite schema so the component queries operate on real tables.
    prepareTestDatabase();

    // Provide a handful of users so the Livewire paginator has data to evaluate.
    User::factory()->create(['name' => 'Buddy Barker']);
    User::factory()->create(['name' => 'Luna Howl']);

    // Running the component through Livewire::test ensures lifecycle hooks and pagination stay aligned with production.
    Livewire::test(FollowList::class)
        ->set('search', 'Buddy')
        ->assertViewHas('followers', function ($followers) {
            // The followers payload should be a paginated collection constrained to the matching record.
            return $followers->count() === 1
                && $followers->first()->name === 'Buddy Barker';
        })
        ->set('search', 'Luna')
        ->assertViewHas('followers', function ($followers) {
            return $followers->count() === 1
                && $followers->first()->name === 'Luna Howl';
        });
});
