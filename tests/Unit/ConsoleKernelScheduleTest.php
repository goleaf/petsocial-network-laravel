<?php

use App\Console\Kernel;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Events\Dispatcher;

it('defines the digest command when the schedule method is invoked directly', function () {
    // Build a fresh schedule instance so the unit test has a controlled environment.
    $schedule = new Schedule(app());

    // Instantiate the console kernel with the application container and event dispatcher dependencies.
    $kernel = new Kernel(app(), app(Dispatcher::class));

    // Use reflection because the schedule method is protected on the base kernel implementation.
    $scheduleMethod = new ReflectionMethod(Kernel::class, 'schedule');
    $scheduleMethod->setAccessible(true);

    // Invoke the schedule definition and populate the provided Schedule instance with events.
    $scheduleMethod->invoke($kernel, $schedule);

    // Find the digest command so the test can confirm that it was registered correctly.
    $digestEvent = collect($schedule->events())->first(
        fn ($event) => str_contains($event->command, 'notifications:send-digests')
    );

    // The schedule should include the digest command and maintain the hourly cron expression.
    expect($digestEvent)->not->toBeNull();
    expect($digestEvent->expression)->toBe('0 * * * *');
});
