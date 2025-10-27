<?php

use App\Models\AccountRecovery;
use App\Models\User;
use Illuminate\Support\Facades\Password;

use function Pest\Laravel\post;

/**
 * Account recovery logging expectations for password reset requests.
 */
describe('Account recovery logging', function () {
    it('records successful reset link dispatches with metadata', function () {
        // Establish a known user whose recovery attempt we can audit.
        $user = User::factory()->create([
            'email' => 'mila@example.com',
        ]);

        // Simulate a successful broker response for deterministic assertions.
        Password::shouldReceive('sendResetLink')
            ->once()
            ->with(['email' => 'mila@example.com'])
            ->andReturn(Password::RESET_LINK_SENT);

        post('/forgot-password', ['email' => 'mila@example.com'], [
            'REMOTE_ADDR' => '203.0.113.50',
            'HTTP_USER_AGENT' => 'Pest Browser',
        ])->assertSessionHas('status');

        $recovery = AccountRecovery::first();

        expect($recovery)->not->toBeNull();
        expect($recovery->user_id)->toBe($user->id);
        expect($recovery->status)->toBe('sent');
        expect($recovery->ip_address)->toBe('203.0.113.50');
        expect($recovery->user_agent)->toBe('Pest Browser');
        expect($recovery->requested_at)->not->toBeNull();
    });

    it('logs failures for unknown emails without associating a user', function () {
        // Force the broker to indicate a throttled or invalid email state.
        Password::shouldReceive('sendResetLink')
            ->once()
            ->with(['email' => 'unknown@example.com'])
            ->andReturn(Password::RESET_THROTTLED);

        post('/forgot-password', ['email' => 'unknown@example.com'])
            ->assertSessionHasErrors('email');

        $recovery = AccountRecovery::first();

        expect($recovery)->not->toBeNull();
        expect($recovery->user_id)->toBeNull();
        expect($recovery->status)->toBe('failed');
    });
});
