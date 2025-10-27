<?php

use App\Http\Livewire\Common\Friend\Button;
use App\Models\Friendship;
use App\Models\User;
use Livewire\Livewire;

if (!class_exists('FakeFilamentAction')) {
    // Provide a lightweight stand-in for Filament actions so the test can focus on integration logic.
    class FakeFilamentAction
    {
        public string $name;

        protected $callback;

        public static function make(string $name): self
        {
            $instance = new self();
            $instance->name = $name;

            return $instance;
        }

        public function action(callable $callback): self
        {
            $this->callback = $callback;

            return $this;
        }

        public function execute(): void
        {
            // Execute the stored callback to simulate Filament invoking the action handler.
            ($this->callback)();
        }
    }
}

it('can be triggered from a filament-style action wrapper', function () {
    // Create the user pairing that will participate in the simulated action.
    $requester = User::factory()->create();
    $target = User::factory()->create();

    // Authenticate the requester so the component authorizes the action call.
    $this->actingAs($requester);

    // Mount the Livewire component that the fake Filament action will interact with.
    $component = Livewire::test(Button::class, [
        'entityType' => 'user',
        'entityId' => $requester->id,
        'targetId' => $target->id,
    ]);

    // Configure the fake action to call the Livewire method, mimicking Filament behaviour.
    FakeFilamentAction::make('sendFriendRequest')
        ->action(fn () => $component->call('sendRequest'))
        ->execute();

    // Confirm the component state and database reflect the action side effects.
    $component->assertSet('status', 'sent_request');

    expect(Friendship::where('sender_id', $requester->id)
        ->where('recipient_id', $target->id)
        ->where('status', Friendship::STATUS_PENDING)
        ->exists())->toBeTrue();
});
