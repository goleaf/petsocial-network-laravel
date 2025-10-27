<?php

use App\Console\Kernel;
use Illuminate\Console\Scheduling\Schedule;
use Livewire\Component;
use Livewire\Livewire;

it('allows a livewire component to inspect the digest schedule registration', function () {
    // Create an inline Livewire component so the test can observe scheduler state during mount.
    Livewire::test(new class extends Component {
        /**
         * The scheduled command list collected during the component boot cycle.
         */
        public array $commands = [];

        public function mount(Kernel $kernel): void
        {
            // Obtain a fresh schedule instance to avoid crosstalk with other tests.
            $schedule = new Schedule(app());

            // Invoke the kernel schedule definition so the new schedule is populated for inspection.
            $scheduleMethod = new ReflectionMethod(Kernel::class, 'schedule');
            $scheduleMethod->setAccessible(true);
            $scheduleMethod->invoke($kernel, $schedule);

            // Capture the underlying command strings so the outer test can assert against them.
            $this->commands = collect($schedule->events())
                ->map(fn ($event) => $event->command)
                ->all();
        }

        public function render(): string
        {
            // The component does not need to output UI for this verification-focused scenario.
            return <<<'blade'
                <div></div>
            blade;
        }
    })
        // Confirm that the captured command list includes the notifications digest command entry.
        ->assertSet('commands', fn (array $commands) => collect($commands)->contains(
            fn ($command) => str_contains($command, 'notifications:send-digests')
        ));
});
