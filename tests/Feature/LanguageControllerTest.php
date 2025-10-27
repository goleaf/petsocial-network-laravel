<?php

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

beforeEach(function (): void {
    // Reset the locale and session to guarantee a predictable starting point for every scenario.
    App::setLocale(config('app.fallback_locale'));
    Session::flush();
});

it('switches to a supported locale and redirects back to the previous page', function (): void {
    // Visit the language switch route from the settings page so the redirect helper has context.
    $response = $this->from('/settings')->get(route('language.switch', ['locale' => 'ru']));

    // Confirm the controller sends the browser back to the originating location.
    $response->assertRedirect('/settings');

    // Validate that both the session and the framework now reflect the selected locale.
    expect(Session::get('locale'))->toBe('ru');
    expect(App::getLocale())->toBe('ru');
});

it('falls back to the configured default when the locale is unsupported', function (): void {
    // Attempt to switch to an invalid locale code so the fallback path is exercised.
    $response = $this->from('/dashboard')->get(route('language.switch', ['locale' => 'fr']));

    // Even on invalid input the user should land back on the original page.
    $response->assertRedirect('/dashboard');

    // The controller should write the fallback locale into session and the application container.
    expect(Session::get('locale'))->toBe(config('app.fallback_locale'));
    expect(App::getLocale())->toBe(config('app.fallback_locale'));
});
