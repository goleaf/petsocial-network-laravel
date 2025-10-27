<?php

use App\Http\Livewire\Common\NotificationCenter;
use App\Models\Pet;
use App\Models\PetNotification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;
use Symfony\Component\HttpKernel\Exception\HttpException;

uses(RefreshDatabase::class);

it('prevents members from mounting the notification center for other users', function (): void {
    // Flush cached counters so the Livewire component performs a fresh authorization check.
    Cache::flush();

    // Create two distinct members to verify cross-account access restrictions are enforced.
    $viewer = User::factory()->create();
    $target = User::factory()->create();

    // Authenticate as the viewer who should be denied when targeting another member.
    $this->actingAs($viewer);

    // The component should abort with a 403 when attempting to view another user's notification feed.
    expect(fn () => Livewire::test(NotificationCenter::class, ['entityType' => 'user', 'entityId' => $target->id]))
        ->toThrow(HttpException::class);
});

it('allows pet owners to review notifications for their animals', function (): void {
    // Reset cached unread counters to avoid leaking state between component mounts.
    Cache::flush();

    // Create a pet owner alongside their pet and a sender pet for relationship hydration.
    $owner = User::factory()->create();
    $pet = Pet::factory()->for($owner)->create();
    $sender = Pet::factory()->create();

    // Store a notification so the component has payloads to paginate for the owner.
    $notification = PetNotification::create([
        'pet_id' => $pet->id,
        'sender_pet_id' => $sender->id,
        'type' => 'friend_request',
        'content' => 'sent your pet a friend request',
        'data' => ['action' => 'friend_request'],
    ]);

    // Authenticate as the owner to satisfy authorization before hydrating the component.
    $this->actingAs($owner);

    // Confirm the Livewire view renders the expected Blade template and exposes the notification.
    Livewire::test(NotificationCenter::class, ['entityType' => 'pet', 'entityId' => $pet->id])
        ->assertViewIs('livewire.common.notification-center')
        ->assertSet('entityType', 'pet')
        ->assertViewHas('notifications', function ($paginator) use ($notification): bool {
            // Ensure the paginator contains exactly the stored notification for the pet owner.
            return $paginator->total() === 1 && $paginator->first()->is($notification);
        });
});
