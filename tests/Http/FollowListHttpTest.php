<?php


it('redirects guests who attempt to reach the followers endpoint', function (): void {
    // Guests should be bounced to the login screen because the followers route is behind the auth middleware.
    $response = $this->get('/followers');

    $response->assertRedirect(route('login'));
});
