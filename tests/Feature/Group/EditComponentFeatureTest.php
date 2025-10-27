<?php

use App\Http\Livewire\Group\Forms\Edit;
use App\Models\Group\Category;
use App\Models\Group\Group;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

it('updates a group and persists media uploads via the edit component', function () {
    // Guarantee the in-memory schema exists before factories begin seeding data.
    prepareTestDatabase();

    // Prime caching and storage layers so the component can run in isolation.
    Cache::flush();
    Storage::fake('public');

    // Create a creator account alongside the initial and replacement categories.
    $creator = User::factory()->create();
    $initialCategory = Category::query()->create([
        'name' => 'City Meetups',
        'slug' => 'city-meetups',
        'description' => 'Local walking crews',
        'display_order' => 1,
        'is_active' => true,
    ]);
    $replacementCategory = Category::query()->create([
        'name' => 'Sunrise Adventures',
        'slug' => 'sunrise-adventures',
        'description' => 'Early riser gatherings',
        'display_order' => 2,
        'is_active' => true,
    ]);

    // Seed a group record that mirrors the state a maintainer would edit from the UI.
    $group = Group::query()->create([
        'name' => 'Morning Walkers',
        'slug' => 'morning-walkers',
        'description' => 'Daily walking buddies exploring town squares.',
        'category_id' => $initialCategory->id,
        'visibility' => Group::VISIBILITY_OPEN,
        'creator_id' => $creator->id,
        'location' => 'Old Town',
    ]);

    // Exercise the Livewire component to confirm validation, upload handling, and persistence.
    $component = Livewire::test(Edit::class, ['group' => $group])
        ->set('name', 'Sunrise Pack')
        ->set('description', 'Coordinated sunrise treks for energetic pets and people alike.')
        ->set('visibility', Group::VISIBILITY_CLOSED)
        ->set('categoryId', $replacementCategory->id)
        ->set('location', 'Sunrise Plaza')
        ->set('coverImage', UploadedFile::fake()->image('new-cover.jpg', 1280, 720))
        ->set('icon', UploadedFile::fake()->image('new-icon.png', 256, 256));

    $component->call('updateGroup')->assertHasNoErrors();

    // Pull the refreshed group to validate metadata and storage side effects.
    $updatedGroup = $group->fresh();

    $component->assertRedirect(route('group.detail', $updatedGroup));
    expect(session('message'))->toBe('Group updated successfully!');
    expect($updatedGroup->only(['name', 'description', 'visibility', 'category_id', 'location']))->toMatchArray([
        'name' => 'Sunrise Pack',
        'description' => 'Coordinated sunrise treks for energetic pets and people alike.',
        'visibility' => Group::VISIBILITY_CLOSED,
        'category_id' => $replacementCategory->id,
        'location' => 'Sunrise Plaza',
    ]);

    // The slug should regenerate using the group name while persisting uploaded paths.
    expect($updatedGroup->slug)->toBe('sunrise-pack');
    expect($updatedGroup->cover_image)->not()->toBeNull();
    expect($updatedGroup->icon)->not()->toBeNull();
    expect(Storage::disk('public')->exists($updatedGroup->cover_image))->toBeTrue();
    expect(Storage::disk('public')->exists($updatedGroup->icon))->toBeTrue();

    // Reset caching so later tests receive a clean slate.
    Cache::flush();
});
