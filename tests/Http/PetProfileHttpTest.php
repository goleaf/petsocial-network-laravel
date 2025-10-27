<?php

use App\Http\Livewire\Pet\PetProfile;
use App\Models\Pet;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\FileViewFinder;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

/**
 * HTTP tests ensure the Livewire route responds with the correct status codes.
 */
beforeEach(function (): void {
    // Rebuild the in-memory SQLite schema so factories can persist models safely.
    prepareTestDatabase();

    // Capture the original view paths so we can restore them after each scenario.
    /** @var FileViewFinder $viewFinder */
    $viewFinder = app('view')->getFinder();
    $this->originalViewPaths = $viewFinder->getPaths();

    // Prepend the simplified test view directory to avoid heavy nested component dependencies.
    $viewFinder->setPaths(array_merge([
        resource_path('views/tests'),
    ], $this->originalViewPaths));

    // Ensure no cached profile fragments leak between individual HTTP requests.
    Cache::flush();
});

afterEach(function (): void {
    // Restore the framework's original view search paths to avoid leaking state across tests.
    /** @var FileViewFinder $viewFinder */
    $viewFinder = app('view')->getFinder();
    $viewFinder->setPaths($this->originalViewPaths ?? $viewFinder->getPaths());

    // Clear the caches again so subsequent suites begin with deterministic state.
    Cache::flush();
});

describe('pet profile HTTP access control', function (): void {
    it('allows the owner to open their private pet profile', function (): void {
        // Create the authenticated owner and a private pet profile tied to them.
        $owner = User::factory()->create();
        $pet = Pet::factory()->for($owner)->create([
            'is_public' => false,
        ]);
        actingAs($owner);

        // The owner should successfully load the Livewire-backed profile route.
        get(route('pet.profile', ['pet' => $pet->id]))
            ->assertOk()
            ->assertSee($pet->name);
    });

    it('returns a forbidden response to non-owners viewing private profiles', function (): void {
        // Create the legitimate owner, an unauthorized viewer, and the private pet profile.
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $pet = Pet::factory()->for($owner)->create([
            'is_public' => false,
        ]);
        actingAs($intruder);

        // Attempting to visit the profile should yield a 403 status for unauthorized users.
        get(route('pet.profile', ['pet' => $pet->id]))
            ->assertForbidden();
    });
});

it('embeds the Livewire pet profile component within the HTTP response payload', function () {
    // Reset cached fragments so the component renders a fresh view inside the response body.
    Cache::flush();

    // Authenticate the owner and visit the profile route to capture the rendered HTML payload.
    $owner = User::factory()->create();
    $pet = Pet::factory()->for($owner)->create();
    actingAs($owner);
    $response = get(route('pet.profile', $pet));

    // Confirm the HTTP response renders the Livewire component marker for PetProfile.
    $response->assertOk();
    $response->assertSeeLivewire(PetProfile::class);
});
