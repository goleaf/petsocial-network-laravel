<?php

use App\Http\Livewire\Pet\PetProfile;
use App\Models\Pet;
use App\Models\PetActivity;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use function Pest\Laravel\actingAs;

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

    // Toggle the activity panel and confirm the rendered data includes the entry.
    Livewire::test(PetProfile::class, ['petId' => $pet->id])
        ->assertSet('showActivities', false)
        ->call('toggleActivities')
        ->assertSet('showActivities', true)
        ->call('render')
        ->assertViewHas('recentActivities', function ($collection) use ($activity) {
            // Validate that the cached collection returns the freshly created activity.
            return $collection->contains(fn ($item) => $item->id === $activity->id);
        });
});

it('renders the pet profile Blade view with ownership context', function () {
    // Clear caches so the Livewire render cycle fetches a fresh dataset.
    Cache::flush();

    // Authenticate as the pet owner to satisfy the access guard inside the component.
    $owner = User::factory()->create();
    $pet = Pet::factory()->for($owner)->create();
    actingAs($owner);

    // Render the component and assert the Blade view and critical data bindings are intact.
    Livewire::test(PetProfile::class, ['petId' => $pet->id])
        ->assertViewIs('livewire.pet.profile')
        ->assertViewHas('pet', fn ($resolvedPet) => $resolvedPet->is($pet))
        ->assertViewHas('isOwner', true);
});
