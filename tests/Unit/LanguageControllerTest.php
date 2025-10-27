<?php

use App\Http\Controllers\LanguageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\RedirectResponse;

beforeEach(function (): void {
    // Reset the locale state and bootstrap the session so the controller can persist data.
    App::setLocale(config('app.fallback_locale'));
    Session::flush();
    Session::start();
});

it('returns a redirect response while applying a supported locale', function (): void {
    // Seed the session with a previous location to give redirect()->back() a target.
    session()->setPreviousUrl('https://example.test/settings');

    // Create a request object that mimics visiting the language switch endpoint.
    $request = Request::create('/language/lt', 'GET', [], [], [], ['HTTP_REFERER' => 'https://example.test/settings']);

    // Share the request instance with the container so helpers like redirect()->back() can inspect it.
    app()->instance('request', $request);

    // Invoke the controller directly to exercise the core logic without the HTTP kernel.
    $response = (new LanguageController())->switchLang($request, 'lt');

    // Validate the return type and the intended redirect destination.
    expect($response)->toBeInstanceOf(RedirectResponse::class);
    expect($response->getTargetUrl())->toBe('https://example.test/settings');

    // Confirm the locale mutation took effect inside both the session and the framework container.
    expect(Session::get('locale'))->toBe('lt');
    expect(App::getLocale())->toBe('lt');
});

it('falls back gracefully when asked to switch to an unknown locale', function (): void {
    // Provide a previous URL and referer so the controller can redirect appropriately.
    session()->setPreviousUrl('https://example.test/profile');
    $request = Request::create('/language/xx', 'GET', [], [], [], ['HTTP_REFERER' => 'https://example.test/profile']);

    // Publish the request to the container for helpers that rely on the global request instance.
    app()->instance('request', $request);

    // Trigger the locale switcher with an unsupported code.
    $response = (new LanguageController())->switchLang($request, 'xx');

    // The response should still be a redirect pointing back to the initiating page.
    expect($response)->toBeInstanceOf(RedirectResponse::class);
    expect($response->getTargetUrl())->toBe('https://example.test/profile');

    // Because the locale is invalid, the fallback configuration should be used instead.
    expect(Session::get('locale'))->toBe(config('app.fallback_locale'));
    expect(App::getLocale())->toBe(config('app.fallback_locale'));
});
