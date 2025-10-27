<?php

use App\Http\Livewire\Group\Management\Index;
use App\Models\Group\Category;
use App\Models\Group\Group;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

it('exposes group datasets that can populate a Filament table widget', function (): void {
    // Authenticate a moderator to mirror the administrative role that would open a Filament table.
    $moderator = User::factory()->create(['role' => 'moderator']);
    actingAs($moderator);

    // Refresh category caches so the Livewire component pulls in the new fixture record.
    Cache::flush();
    $category = Category::query()->create([
        'name' => 'Operations',
        'slug' => 'operations',
        'is_active' => true,
    ]);

    // Create a group and attach members to simulate data that Filament would render in a table widget.
    $group = Group::query()->create([
        'name' => 'Filament Ready',
        'slug' => Group::generateUniqueSlug('Filament Ready'),
        'description' => 'Internal planning hub for facilitators.',
        'category_id' => $category->id,
        'visibility' => Group::VISIBILITY_OPEN,
        'creator_id' => $moderator->id,
    ]);

    $group->members()->syncWithoutDetaching([
        $moderator->id => [
            'role' => 'admin',
            'status' => 'active',
            'joined_at' => now(),
        ],
    ]);

    // The component should yield a paginator whose records can hydrate a Filament style data table.
    Livewire::test(Index::class)
        ->assertViewHas('groups', function (LengthAwarePaginator $groups) use ($group): bool {
            $table = new class {
                /**
                 * Captured table rows ready for assertion.
                 */
                public array $records = [];

                /**
                 * Mimic Filament's table hydration by normalising model attributes into arrays.
                 */
                public function fill(iterable $records): void
                {
                    foreach ($records as $record) {
                        $this->records[] = [
                            'name' => $record->name,
                            'visibility' => $record->visibility,
                            'members' => $record->members_count,
                        ];
                    }
                }
            };

            $table->fill($groups);

            // Validate the transformed payload contains the expected attributes for Filament columns.
            return count($table->records) === 1
                && $table->records[0]['name'] === $group->name
                && $table->records[0]['visibility'] === Group::VISIBILITY_OPEN
                && $table->records[0]['members'] === 1;
        });
});
