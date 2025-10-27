<?php

use App\Http\Livewire\Pet\Notifications;
use App\Models\Pet;
use App\Models\PetNotification;
use App\Models\User;
use Illuminate\Support\Facades\Route;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

/**
 * Register a temporary route that resolves the Livewire component for HTTP assertions.
 */
function registerPetNotificationsTestRoute(): void
{
    // Avoid redefining the route when multiple tests execute in this file.
    if (Route::has('test.pet.notifications')) {
        return;
    }

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
}

// HTTP level coverage simulates the component being resolved through a standard GET endpoint.
it('returns an ok response when the owner loads the notifications route', function (): void {
    // Wire up the temporary HTTP route so we can assert against a real response payload.
    registerPetNotificationsTestRoute();

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

it('returns a forbidden response when a viewer queries another pets notifications', function (): void {
    // Register the route that proxies the Livewire component into an HTTP context.
    registerPetNotificationsTestRoute();

    // Create both the owner and a separate viewer along with an unread notification.
    $owner = User::factory()->create();
    $viewer = User::factory()->create();
    $pet = Pet::factory()->create(['user_id' => $owner->id]);
    PetNotification::create([
        'pet_id' => $pet->id,
        'sender_pet_id' => null,
        'type' => 'friend_request',
        'content' => 'sent you a friend request',
        'data' => ['action' => 'friend_request'],
    ]);

    actingAs($viewer);

    // Because the viewer is not the owner, the Livewire mount should abort with a 403.
    get(route('test.pet.notifications', $pet->id))
        ->assertForbidden();
});
