<?php

namespace Filament\Tables {
    if (! class_exists(Table::class)) {
        /**
         * Lightweight stand-in for Filament's table builder so the test suite can exercise integrations.
         */
        class Table
        {
            /**
             * @var array<int, array<string, mixed>>
             */
            public array $records = [];

            public function records(array $records): static
            {
                // Persist the provided dataset so assertions can verify the Livewire payload structure.
                $this->records = $records;

                return $this;
            }
        }
    }
}

namespace {
    use App\Http\Livewire\Common\Friend\Export as FriendExportComponent;
    use function Pest\Laravel\actingAs;

    require_once __DIR__.'/../FriendExportTestHelpers.php';

    it('maps the export payload into a Filament table friendly structure', function (): void {
        [$owner, $friend] = createFriendExportUsers();

        actingAs($owner);

        $component = app(FriendExportComponent::class);
        $component->mount('user', $owner->id);

        $table = new \Filament\Tables\Table();

        $records = $component->getUsersByType()->map(function ($user) {
            return [
                'display_name' => $user->name,
                'handle' => $user->username,
                'primary_email' => $user->email,
            ];
        })->toArray();

        $table->records($records);

        expect($table->records)->toMatchArray($records);
        expect($table->records[0]['handle'])->toBe($friend->username);
    });
}
