<?php

use App\Console\Kernel;
use Illuminate\Console\Scheduling\Schedule;

it('retains the digest command schedule after handling a web request', function () {
    // Issue a basic GET request so the HTTP kernel boots exactly as the application would in production.
    $this->withoutVite();
    $this->get('/')->assertOk();

    // Populate a fresh schedule instance to confirm the console kernel keeps the digest command registered.
    $schedule = new Schedule(app());
    $scheduleMethod = new ReflectionMethod(Kernel::class, 'schedule');
    $scheduleMethod->setAccessible(true);
    $scheduleMethod->invoke(app(Kernel::class), $schedule);

    // Look for the digest command so the test can confirm it survived the HTTP lifecycle.
    $digestEvent = collect($schedule->events())->first(
        fn ($event) => str_contains($event->command, 'notifications:send-digests')
    );

    // The digest command should remain available and on the expected hourly cadence.
    expect($digestEvent)->not->toBeNull();
    expect($digestEvent->expression)->toBe('0 * * * *');
});
