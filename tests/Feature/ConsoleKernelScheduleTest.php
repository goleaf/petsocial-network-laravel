<?php

use App\Console\Kernel;
use Illuminate\Console\Scheduling\Schedule;

it('schedules the digest command on an hourly cadence', function () {
    // Boot the console kernel so that the scheduler contains the most recent command definitions.
    $kernel = app(Kernel::class);
    $kernel->bootstrap();

    // Collect the scheduled events to verify that the notifications digest command is present.
    $events = collect(app(Schedule::class)->events());

    // Locate the digest command within the schedule so we can assert the expected cron expression.
    $digestEvent = $events->first(fn ($event) => str_contains($event->command, 'notifications:send-digests'));

    // Confirm that the digest command exists and that it runs once per hour as designed.
    expect($digestEvent)->not->toBeNull();
    expect($digestEvent->expression)->toBe('0 * * * *');
});
