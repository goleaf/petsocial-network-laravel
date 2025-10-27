<?php

use App\Http\Livewire\Pet\PetManagement;
use App\Models\Pet;
use App\Models\PetActivity;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

beforeEach(function (): void {
    // Rebuild the transient database schema before every interaction-focused scenario.
    prepareTestDatabase();

    // Guarantee the pet activities table exists so media cleanup assertions operate without migration stubs.
    Schema::dropIfExists('pet_activities');
    Schema::create('pet_activities', function (Blueprint $table): void {
        // Provide the minimal set of columns referenced by the PetActivity model within these tests.
        $table->id();
        $table->foreignId('pet_id');
        $table->string('type')->nullable();
        $table->string('description')->nullable();
        $table->timestamp('happened_at')->nullable();
        $table->string('image')->nullable();
        $table->boolean('is_public')->default(true);
        $table->timestamps();
    });

    // Register lightweight route stubs so the view links resolve during Livewire renders.
    if (! Route::has('pet.profile')) {
        Route::name('pet.profile')->get('/testing/pets/{pet}', fn () => '');
    }

    if (! Route::has('pet.friends')) {
        Route::name('pet.friends')->get('/testing/pets/{pet}/friends', fn () => '');
    }

    if (! Route::has('pet.activities')) {
        Route::name('pet.activities')->get('/testing/pets/{pet}/activities', fn () => '');
    }

    if (! Route::has('pet.medical-records')) {
        Route::name('pet.medical-records')->get('/testing/pets/{pet}/medical-records', fn () => '');
    }

    // Provide lightweight relationship hooks so detach calls operate without the full friendship module.
    Pet::resolveRelationUsing('friends', function (Pet $model) {
        return $model->belongsToMany(Pet::class, 'pet_friendships', 'pet_id', 'friend_pet_id');
    });

    Pet::resolveRelationUsing('friendOf', function (Pet $model) {
        return $model->belongsToMany(Pet::class, 'pet_friendships', 'friend_pet_id', 'pet_id');
    });
});

/**
 * Livewire interaction tests for the pet management component.
 */
it('allows a user to create a pet with an uploaded avatar', function (): void {
    // Prepare the filesystem and cache so uploads can be asserted without touching disk.
    Storage::fake('public');
    Cache::flush();

    // Authenticate a user who will own the pet record.
    $user = User::factory()->create();
    actingAs($user);

    // Drive the Livewire component through the save workflow.
    Livewire::test(PetManagement::class)
        ->set('name', 'Ranger')
        ->set('type', 'dog')
        ->set('breed', 'Collie')
        ->set('birthdate', '2020-01-01')
        ->set('location', 'Austin, TX')
        ->set('favorite_food', 'Peanut Butter')
        ->set('favorite_toy', 'Frisbee')
        ->set('bio', 'Trail ready and energetic.')
        ->set('avatar', UploadedFile::fake()->image('ranger.jpg'))
        ->call('save')
        ->assertHasNoErrors();

    // Validate the pet persisted and the avatar landed on the public disk.
    $pet = Pet::first();
    expect($pet)->not->toBeNull();
    expect($pet->name)->toBe('Ranger');
    Storage::disk('public')->assertExists($pet->avatar);
});

it('updates existing pets, replaces avatars, and clears related caches', function (): void {
    // Use a fake disk so avatar updates can be asserted safely.
    Storage::fake('public');

    // Establish an authenticated owner with an existing pet record.
    $user = User::factory()->create();
    actingAs($user);
    $pet = Pet::factory()->for($user)->create([
        'name' => 'Milo',
        'avatar' => 'pet-avatars/original.jpg',
    ]);
    Storage::disk('public')->put('pet-avatars/original.jpg', 'avatar-content');

    // Seed caches that should be purged when the update runs.
    Cache::put("pet_{$pet->id}_friend_ids", collect([1, 2]));
    Cache::put("pet_{$pet->id}_recent_activities_5", collect([5]));

    // Drive the edit/update flow.
    Livewire::test(PetManagement::class)
        ->call('edit', $pet->id)
        ->set('name', 'Milo Updated')
        ->set('avatar', UploadedFile::fake()->image('replacement.jpg'))
        ->call('update')
        ->assertHasNoErrors();

    // Confirm the persisted pet data and file storage reflect the new state.
    $pet->refresh();
    expect($pet->name)->toBe('Milo Updated');
    Storage::disk('public')->assertMissing('pet-avatars/original.jpg');
    Storage::disk('public')->assertExists($pet->avatar);

    // Ensure pet specific caches have been purged.
    expect(Cache::has("pet_{$pet->id}_friend_ids"))->toBeFalse();
    expect(Cache::has("pet_{$pet->id}_recent_activities_5"))->toBeFalse();
});

it('deletes pets, removes related media, and clears caches', function (): void {
    // Fake the public disk so removal of avatar and activity images can be asserted.
    Storage::fake('public');

    // Create an owner and a pet with associated activity media.
    $user = User::factory()->create();
    actingAs($user);
    $pet = Pet::factory()->for($user)->create([
        'avatar' => 'pet-avatars/delete-me.jpg',
    ]);
    Storage::disk('public')->put('pet-avatars/delete-me.jpg', 'avatar');

    $activityImage = 'pet-activities/activity.jpg';
    PetActivity::create([
        'pet_id' => $pet->id,
        'type' => 'walk',
        'description' => 'Morning walk',
        'happened_at' => now(),
        'image' => $activityImage,
    ]);
    Storage::disk('public')->put($activityImage, 'activity');

    // Prime caches that should be forgotten when the deletion completes.
    Cache::put("pet_{$pet->id}_friend_ids", collect([3]));
    Cache::put("pet_{$pet->id}_recent_activities_5", collect([7]));

    // Trigger the deletion through the Livewire component.
    Livewire::test(PetManagement::class)
        ->call('delete', $pet->id)
        ->assertHasNoErrors();

    // Validate the pet row is gone and that related files were removed.
    expect(Pet::find($pet->id))->toBeNull();
    Storage::disk('public')->assertMissing('pet-avatars/delete-me.jpg');
    Storage::disk('public')->assertMissing($activityImage);

    // Verify caches tied to the pet were flushed.
    expect(Cache::has("pet_{$pet->id}_friend_ids"))->toBeFalse();
    expect(Cache::has("pet_{$pet->id}_recent_activities_5"))->toBeFalse();
});
