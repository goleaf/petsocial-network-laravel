<?php

use App\Http\Livewire\Pet\Notifications;
use App\Models\Pet;
use App\Models\PetNotification;
use App\Models\User;
use Symfony\Component\HttpKernel\Exception\HttpException;

use function Pest\Laravel\actingAs;

// Feature coverage ensures authorization rules remain enforced for the pet notifications panel.
it('blocks non owners from mounting the pet notifications component', function (): void {
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

it('allows the owner to resolve the livewire view with notifications data', function (): void {
    // Create an owner with a pet and a notification so the Livewire render step has content to expose.
    $owner = User::factory()->create();
    $pet = Pet::factory()->create(['user_id' => $owner->id]);
    PetNotification::create([
        'pet_id' => $pet->id,
        'sender_pet_id' => null,
        'type' => 'activity',
        'content' => 'completed a training session',
        'data' => ['action' => 'activity', 'activity_id' => 42],
    ]);

    actingAs($owner);

    // Resolve the component as Livewire would and inspect the rendered view metadata.
    $component = app(Notifications::class);
    $component->mount($pet->id);
    $view = $component->render();

    // The view should point at the Blade template wired to the component and contain a paginator instance.
    expect($view->name())->toBe('livewire.pet.notifications');

    $data = $view->getData();
    expect($data)->toHaveKey('notifications');
    expect($data['notifications']->total())->toBe(1);
});
