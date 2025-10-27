<?php

use App\Http\Middleware\TrustHosts;

it('builds the regex pattern for the configured application host', function (): void {
    // Configure a custom application URL so the middleware builds a predictable host regex.
    config(['app.url' => 'https://petsocial.test']);

    // Instantiate the middleware directly to read the resolved host patterns.
    $middleware = new TrustHosts(app());

    $hosts = $middleware->hosts();
    $expectedHost = parse_url(config('app.url'), PHP_URL_HOST);

    expect($hosts)
        ->toBeArray()
        ->toHaveCount(1);

    expect($hosts[0])
        ->toBe('^(.+\\.)?'.preg_quote($expectedHost).'$');
});
