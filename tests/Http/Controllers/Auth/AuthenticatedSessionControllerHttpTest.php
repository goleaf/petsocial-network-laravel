<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

use function Pest\Laravel\post;

// HTTP level assertions focused on request validation and redirects for the session controller.
describe('Authenticated session HTTP validation', function (): void {
    it('requires both email and password fields', function (): void {
        // Submit an empty payload to confirm the validation rules fire for missing data.
        $response = post(route('login'), []);

        $response->assertSessionHasErrors(['email', 'password']);
    });

    it('enforces a valid email address format', function (): void {
        // Provide a clearly invalid email string to hit the email validator.
        $response = post(route('login'), [
            'email' => 'not-an-email',
            'password' => 'some-password',
        ]);

        $response->assertSessionHasErrors(['email']);
    });

    it('redirects to the originally intended URL after successful authentication', function (): void {
        // Create an active user so credentials succeed.
        $user = User::factory()->create([
            'email' => 'redirect@example.com',
            'password' => Hash::make('Password123!'),
        ]);

        // Prime the session with an intended destination to verify redirect()->intended behaviour.
        session()->put('url.intended', route('pets'));

        // Submit the correct credentials for the seeded user.
        $response = post(route('login'), [
            'email' => 'redirect@example.com',
            'password' => 'Password123!',
        ]);

        $response->assertRedirect(route('pets'));
    });
});
