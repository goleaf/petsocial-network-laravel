<?php

use App\Models\User;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

it('redirects guests away from the messages page', function (): void {
    // Guests should be redirected to login to protect private conversations.
    get(route('messages'))->assertRedirect(route('login'));
});

it('allows authenticated users to load the messages page', function (): void {
    // Authenticate a user to ensure the messages route becomes accessible.
    $user = User::factory()->create();
    $user->setRelation('friends', collect());
    actingAs($user);

    // Confirm the protected page renders successfully for authorised members.
    get(route('messages'))->assertOk();
});
