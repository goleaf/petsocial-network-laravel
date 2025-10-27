<?php

use App\Http\Livewire\Common\PostManager;
use App\Models\Pet;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('exposes render data compatible with Filament panel embedding', function (): void {
    // Authenticate as the account owner so the component renders the composer and feed.
    $author = User::factory()->create([
        'profile_visibility' => 'public',
        'privacy_settings' => User::PRIVACY_DEFAULTS,
    ]);

    $this->actingAs($author);

    // Ensure a related pet exists so Filament dashboards can display the pet selector.
    $pet = Pet::factory()->create([
        'user_id' => $author->id,
        'name' => 'Shadow',
    ]);

    // Seed a post so the rendered paginator contains data for the Filament table region.
    Post::create([
        'user_id' => $author->id,
        'content' => 'Filament surface test post',
        'pet_id' => $pet->id,
    ]);

    $component = new PostManager();
    $component->mount('user', $author->id);

    // Rendering should provide the canonical view and expected dataset keys for Filament widgets.
    $view = $component->render();
    $data = $view->getData();

    expect($view->getName())->toBe('livewire.common.post-manager')
        ->and($data)->toHaveKeys(['posts', 'userPets'])
        ->and($data['posts']->count())->toBe(1)
        ->and($data['userPets']->first()->is($pet))->toBeTrue();
});
