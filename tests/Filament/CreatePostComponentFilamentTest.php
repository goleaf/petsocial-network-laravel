<?php

use App\Http\Livewire\Content\CreatePost;
use App\Models\User;
use Livewire\Livewire;

/**
 * Filament-oriented checks to guarantee the component's rules align with form expectations.
 */
it('shares validation rules that Filament form builders can consume', function () {
    // Skip gracefully when the Filament package is absent in the local test environment.
    if (! interface_exists('Filament\\Forms\\Contracts\\HasForms')) {
        $this->markTestSkipped('Filament is not installed, so compatibility checks are skipped.');
    }

    // Authenticate so the component can mount and expose its validation rule set.
    $author = User::factory()->create();
    $this->actingAs($author);

    $rules = Livewire::test(CreatePost::class)->instance()->getRules();

    // Ensure every composer field publishes constraints Filament can mirror in its schema.
    expect($rules)->toMatchArray([
        'content' => 'required|max:1000',
        'tags' => 'nullable|string|max:255',
        'pet_id' => 'nullable|exists:pets,id',
        'visibility' => 'required|in:public,friends,private',
        'images.*' => 'nullable|image|max:5120|mimes:jpg,jpeg,png,gif',
    ]);
});
