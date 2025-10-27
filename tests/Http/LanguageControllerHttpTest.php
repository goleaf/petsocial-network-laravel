<?php

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

beforeEach(function (): void {
    // Ensure every HTTP scenario starts with a clean locale context and empty session storage.
    App::setLocale(config('app.fallback_locale'));
    Session::flush();
});

it('responds with a redirect when switching to a supported locale', function (): void {
    // Perform an HTTP request that mimics a user toggling the locale from the feed view.
    $response = $this->from('/feed')->get(route('language.switch', ['locale' => 'lt']));

    // The controller should emit a 302 redirect back to the originating page.
    $response->assertStatus(302)->assertRedirect('/feed');

    // Confirm the locale persisted to the session so the browser can load translated content.
    $response->assertSessionHas('locale', 'lt');
});

it('keeps the response well-formed even when the locale is invalid', function (): void {
    // Fire the controller with an unsupported locale code to exercise the guard clause.
    $response = $this->from('/profile')->get(route('language.switch', ['locale' => 'es']));

    // The HTTP layer should still return a redirect so the UX remains consistent.
    $response->assertStatus(302)->assertRedirect('/profile');

    // The fallback locale should be stored to keep the interface readable for the member.
    $response->assertSessionHas('locale', config('app.fallback_locale'));
});
