<?php

use App\Http\Livewire\Pet\Notifications;
use App\Models\Pet;
use App\Models\PetNotification;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

use function Pest\Laravel\actingAs;

// Filament integrations consume Livewire view data, so we validate the payload mirrors widget expectations.
it('exposes paginated notifications with eager loaded relations for admin panels', function (): void {
    // Assemble an owner, a sender pet, and a notification record to mirror dashboard output.
    $owner = User::factory()->create();
    $pet = Pet::factory()->create(['user_id' => $owner->id]);
    $sender = Pet::factory()->create(['user_id' => $owner->id]);

    PetNotification::create([
        'pet_id' => $pet->id,
        'sender_pet_id' => $sender->id,
        'type' => 'activity',
        'content' => 'logged a new trick',
        'data' => ['action' => 'activity', 'activity_id' => 10, 'activity_type' => 'trick'],
    ]);

    actingAs($owner);

    // Fetch the rendered data and ensure it matches the structure Filament resource tables expect.
    $component = app(Notifications::class);
    $component->mount($pet->id);
    $viewData = $component->render()->getData()['notifications'];

    expect($viewData)->toBeInstanceOf(LengthAwarePaginator::class)
        ->and($viewData->items()[0]->relationLoaded('senderPet'))->toBeTrue();
});
