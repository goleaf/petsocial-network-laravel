<?php

use App\Http\Livewire\Common\NotificationCenter;
use App\Models\PetNotification;
use App\Models\UserNotification;
use ReflectionClass;

it('maps notification metadata helpers to the correct models', function (): void {
    // Instantiate the component directly so we can interrogate its protected helpers in isolation.
    $component = new NotificationCenter();
    $reflector = new ReflectionClass(NotificationCenter::class);

    // Prepare invokers for the protected helper methods that power the notification queries.
    $modelMethod = $reflector->getMethod('getNotificationModel');
    $modelMethod->setAccessible(true);
    $columnMethod = $reflector->getMethod('getEntityColumn');
    $columnMethod->setAccessible(true);
    $senderMethod = $reflector->getMethod('getSenderRelationship');
    $senderMethod->setAccessible(true);

    // Validate the user branch resolves the UserNotification model and related metadata.
    $component->entityType = 'user';
    expect($modelMethod->invoke($component))->toBe(UserNotification::class)
        ->and($columnMethod->invoke($component))->toBe('user_id')
        ->and($senderMethod->invoke($component))->toBe('senderUser');

    // Validate the pet branch resolves the PetNotification model and related metadata.
    $component->entityType = 'pet';
    expect($modelMethod->invoke($component))->toBe(PetNotification::class)
        ->and($columnMethod->invoke($component))->toBe('pet_id')
        ->and($senderMethod->invoke($component))->toBe('senderPet');
});
