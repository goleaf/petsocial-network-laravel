<?php

use App\Http\Livewire\Pet\Notifications;
use App\Models\Pet;
use App\Models\PetNotification;
use App\Models\User;
use Illuminate\Support\Facades\Route;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

// HTTP level coverage simulates the component being resolved through a standard GET endpoint.
it('returns an ok response when the owner loads the notifications route', function (): void {
    // Define a temporary route that maps directly to the Livewire component for the test case.
    Route::get('/test/pets/{petId}/notifications', function (int $petId) {
        $component = app(Notifications::class);
        $component->mount($petId);

        $paginator = $component->render()->getData()['notifications'];

        return response()->json([
            'unread' => $component->unreadCount,
            'total' => $paginator->total(),
        ]);
    })->name('test.pet.notifications');
    Route::getRoutes()->refreshNameLookups();

    // Seed the database with an owner, a pet, and an unread notification so the component has content.
    $owner = User::factory()->create();
    $pet = Pet::factory()->create(['user_id' => $owner->id]);
    PetNotification::create([
        'pet_id' => $pet->id,
        'sender_pet_id' => null,
        'type' => 'friend_request',
        'content' => 'sent you a friend request',
        'data' => ['action' => 'friend_request'],
    ]);

    actingAs($owner);

    // The Livewire powered endpoint should render successfully for the authenticated owner.
    get(route('test.pet.notifications', $pet->id))
        ->assertOk()
        ->assertJson([
            'unread' => 1,
            'total' => 1,
        ]);
});
