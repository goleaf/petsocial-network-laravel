<?php

use App\Http\Livewire\Pet\PetProfile;
use App\Models\Pet;
use App\Models\PetActivity;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use function Pest\Laravel\actingAs;

beforeEach(function (): void {
    // Recreate the in-memory tables required for activity lookups before each scenario.
    prepareTestDatabase();

    // Swap the view paths so the simplified test Blade overrides render without nested Livewire dependencies.
    $viewFinder = app('view')->getFinder();
    $this->originalViewPaths = $viewFinder->getPaths();
    $viewFinder->setPaths(array_merge([resource_path('views/tests')], $this->originalViewPaths));
});

afterEach(function (): void {
    // Restore the original view search paths before concluding the scenario.
    $viewFinder = app('view')->getFinder();
    $viewFinder->setPaths($this->originalViewPaths ?? $viewFinder->getPaths());
});

it('streams recent activities when the timeline tab is toggled on', function () {
    // Clear cached state so the Livewire component fetches fresh data.
    Cache::flush();

    // Guarantee the pet_activities table exists for the relationship queries.
    Schema::dropIfExists('pet_activities');
    Schema::create('pet_activities', function (Blueprint $table) {
        // Minimal columns required by the PetActivity model during the test run.
        $table->id();
        $table->foreignId('pet_id');
        $table->string('type')->nullable();
        $table->string('description')->nullable();
        $table->timestamp('happened_at')->nullable();
        $table->boolean('is_public')->default(true);
        $table->json('data')->nullable();
        $table->boolean('read')->default(false);
        $table->timestamps();
    });

    // Build the authenticated owner and associated pet profile.
    $owner = User::factory()->create();
    $pet = Pet::factory()->for($owner)->create();
    actingAs($owner);

    // Seed a single public activity so the timeline has content to return.
    $activity = PetActivity::query()->create([
        'pet_id' => $pet->id,
        'type' => 'walk',
        'description' => 'Morning park stroll',
        'happened_at' => now()->subHour(),
        'is_public' => true,
        'data' => ['distance' => '2km'],
        'read' => false,
    ]);

    // Register any lightweight route stubs missing from the simplified testing context.
    if (! Route::has('pet.edit')) {
        Route::get('/testing/pets/{pet}/edit', fn () => '')->name('pet.edit');
    }

    // Toggle the activity panel and confirm the rendered data includes the entry.
    Livewire::test(PetProfile::class, ['pet' => $pet->id])
        ->assertSet('showActivities', false)
        ->call('toggleActivities')
        ->assertSet('showActivities', true)
        ->assertViewHas('recentActivities', function ($collection) use ($activity) {
            // Validate that the cached collection returns the freshly created activity.
            return $collection->contains(fn ($item) => $item->id === $activity->id);
        });
});
