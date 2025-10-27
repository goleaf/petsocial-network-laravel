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
    // Initialize the transient sqlite schema so Livewire can hydrate its dependencies.
    prepareTestDatabase();
    preparePetNotificationSchema();

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

// Ensure the same HTTP endpoint denies access when a non-owner attempts to load the notifications feed.
it('returns a forbidden response for non owners accessing the notifications endpoint', function (): void {
    // Refresh the in-memory database to isolate this authorization scenario.
    prepareTestDatabase();
    preparePetNotificationSchema();

    // Register a dedicated route that resolves the Livewire component before returning a response.
    Route::get('/test/pets/{petId}/notifications/forbidden', function (int $petId) {
        $component = app(Notifications::class);
        $component->mount($petId);

        return response()->noContent();
    })->name('test.pet.notifications.forbidden');
    Route::getRoutes()->refreshNameLookups();

    // Seed the database with an owner, viewer, pet, and notification so mount performs its authorization check.
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

    // The endpoint should reject the request because the viewer is not the pet owner.
    get(route('test.pet.notifications.forbidden', $pet->id))->assertForbidden();
});
