<?php

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

it('confirms the application controller extends the framework base controller and imports expected traits', function (): void {
    // Reflect the controller to inspect its inheritance chain and trait usage without instantiating it.
    $reflection = new ReflectionClass(Controller::class);

    // Verify the controller inherits from Laravel's routing base controller to ensure compatibility with framework features.
    expect($reflection->isSubclassOf(BaseController::class))->toBeTrue();

    // Capture the traits applied directly to the controller so validation and authorization helpers remain available.
    $usedTraits = $reflection->getTraitNames();

    // Confirm the authorization trait remains attached which powers authorize(), authorizeForUser(), and policy helpers.
    expect($usedTraits)->toContain(AuthorizesRequests::class);

    // Confirm the validation trait remains attached so controllers can reuse validate() and related shortcuts.
    expect($usedTraits)->toContain(ValidatesRequests::class);
});
