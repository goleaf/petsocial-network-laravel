<?php

use App\Http\Livewire\Pet\Notifications;
use App\Models\Pet;
use App\Models\PetNotification;
use App\Models\User;
use Symfony\Component\HttpKernel\Exception\HttpException;

use function Pest\Laravel\actingAs;

// Feature coverage ensures authorization rules remain enforced for the pet notifications panel.
it('blocks non owners from mounting the pet notifications component', function (): void {
    // Prepare a clean in-memory schema that includes the username column used by factories.
    prepareTestDatabase();
    preparePetNotificationSchema();

    // Create an owner and a second user to exercise the authorization gate.
    $owner = User::factory()->create();
    $viewer = User::factory()->create();

    // Build a pet and a notification owned by the first user.
    $pet = Pet::factory()->create(['user_id' => $owner->id]);
    PetNotification::create([
        'pet_id' => $pet->id,
        'sender_pet_id' => null,
        'type' => 'friend_request',
        'content' => 'sent you a friend request',
        'data' => ['action' => 'friend_request'],
    ]);

    // Attempting to mount as a different user should yield a 403 HTTP exception.
    actingAs($viewer);

    expect(fn () => app(Notifications::class)->mount($pet->id))
        ->toThrow(HttpException::class, 'You do not have permission to view notifications for this pet.');
});

// The dedicated Blade template should exist so Livewire can render the component without runtime errors.
it('exposes the pet notifications blade view for the livewire component', function (): void {
    // Use the shared helper to ensure the Blade lookup operates against a fresh schema.
    prepareTestDatabase();
    preparePetNotificationSchema();

    // Assert the Blade file wired to the component is registered within the view factory.
    expect(view()->exists('livewire.pet.notifications'))->toBeTrue();
});
