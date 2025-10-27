<?php

use App\Http\Livewire\Pet\PetProfile;
use App\Models\Pet;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\View\View;
use Livewire\Livewire;
use Mockery as MockeryFacade;
use function Pest\Laravel\actingAs;

if (! class_exists('PetProfilePlaceholderComponent')) {
    /**
     * Lightweight placeholder used to bypass nested Livewire child components during rendering assertions.
     */
    class PetProfilePlaceholderComponent extends \Livewire\Component
    {
        /**
         * Accept arbitrary parameters to match the parent Blade invocations without mutating state.
         */
        public function mount(...$parameters): void
        {
            // No setup required for placeholder behaviour.
        }

        /**
         * Return a minimal placeholder view so the parent profile can render without errors.
         */
        public function render(): View
        {
            return view('tests.livewire-placeholder');
        }
    }
}

it('renders the pet profile with cached friend metrics for the owner', function () {
    // Ensure a pristine cache layer so the assertions are deterministic.
    Cache::flush();

    // Create the pet owner and authenticate them to load the profile.
    $owner = User::factory()->create();
    actingAs($owner);

    // Build the profiled pet and a companion identifier to simulate cached friendships.
    $pet = Pet::factory()->for($owner)->create();
    $friend = Pet::factory()->create();

    // Prime the profile cache with a partial mock so getFriendIds is available during rendering.
    $cachedPet = MockeryFacade::mock(Pet::class)->makePartial();
    $cachedPet->setRawAttributes($pet->getAttributes(), true);
    $cachedPet->exists = true;
    $cachedPet->setRelation('user', $owner);
    $cachedPet->setRelation('activities', collect());
    $cachedPet->shouldReceive('activities')->andReturn(new class
    {
        /**
         * Provide a fluent interface matching the Eloquent relationship chain used in the Blade view.
         */
        public function whereNotNull($column): self
        {
            return $this;
        }

        /**
         * Preserve chaining compatibility without executing database work.
         */
        public function latest($column): self
        {
            return $this;
        }

        /**
         * Continue the fluent chain before returning the placeholder collection.
         */
        public function limit($value): self
        {
            return $this;
        }

        /**
         * Return an empty collection mirroring the absence of cached photo data.
         */
        public function get()
        {
            return collect();
        }
    });
    $cachedPet->shouldReceive('getFriendIds')->once()->andReturn([$friend->id]);
    $cachedPet->shouldReceive('recentActivities')->andReturn(collect());
    Cache::put("pet_profile_{$pet->id}", $cachedPet, now()->addMinutes(5));

    // Register any lightweight route stubs that the Blade view expects during rendering.
    if (! Route::has('pet.edit')) {
        Route::get('/pets/{pet}/edit', fn () => '')->name('pet.edit');
    }

    // Register stub components for all nested Livewire snippets referenced in the Blade template.
    Livewire::component('common.friend.list', PetProfilePlaceholderComponent::class);
    Livewire::component('common.friend.activity-log', PetProfilePlaceholderComponent::class);
    Livewire::component('common.friend.suggestions', PetProfilePlaceholderComponent::class);
    Livewire::component('common.friend.button', PetProfilePlaceholderComponent::class);
    Livewire::component('common.follow.button', PetProfilePlaceholderComponent::class);

    // Exercise the Livewire component and verify the rendered dataset.
    $viewFinder = app('view')->getFinder();
    $originalViewPaths = $viewFinder->getPaths();
    $viewFinder->setPaths(array_merge([resource_path('views/tests')], $originalViewPaths));

    try {
        Livewire::test(PetProfile::class, ['petId' => $pet->id])
            ->assertViewHas('pet', fn ($resolvedPet) => $resolvedPet->id === $pet->id)
            ->assertViewHas('friendCount', 1)
            ->assertViewHas('isOwner', true);
    } finally {
        // Ensure Mockery expectations are verified and cleaned up even if assertions fail.
        MockeryFacade::close();
        $viewFinder->setPaths($originalViewPaths);
    }

    // Confirm both profile and friend-count caches were warmed by the component lifecycle.
    expect(Cache::has("pet_profile_{$pet->id}"))->toBeTrue();
    expect(Cache::has("pet_{$pet->id}_friend_count"))->toBeTrue();
});

it('exposes the dedicated pet profile Blade view for Livewire rendering', function () {
    // Assert that the Blade template referenced by the Livewire component is present and loadable.
    expect(View::exists('livewire.pet.profile'))->toBeTrue();
});
