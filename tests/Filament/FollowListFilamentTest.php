<?php

use App\Http\Livewire\Common\Follow\FollowList;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

it('produces a paginator payload that Filament tables can consume', function (): void {
    // Build the follower dataset against a schema that mirrors production tables.
    prepareTestDatabase();

    // Populate the database with enough rows to exercise pagination behaviour.
    User::factory()->count(3)->sequence(
        ['name' => 'Nova Follows'],
        ['name' => 'Orbit Follows'],
        ['name' => 'Quasar Follows'],
    )->create();

    // Render the component manually so we can capture the view data emitted to front-end consumers like Filament tables.
    $component = app(FollowList::class);
    $component->search = 'Follows';

    $view = $component->render();
    $followers = $view->getData()['followers'];

    // Filament\Tables\Table expects a LengthAwarePaginator instance to hydrate table records cleanly.
    expect($followers)->toBeInstanceOf(LengthAwarePaginator::class);
    expect($followers->total())->toBe(3);
    expect($followers->getCollection()->pluck('name')->all())
        ->toMatchArray(['Nova Follows', 'Orbit Follows', 'Quasar Follows']);
});
