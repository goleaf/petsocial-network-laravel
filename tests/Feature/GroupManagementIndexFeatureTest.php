<?php

use App\Http\Livewire\Group\Management\Index;
use App\Models\Group\Category;
use App\Models\Group\Group;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

it('creates a group and promotes the creator to admin through the management component', function (): void {
    // Fake the public disk so uploaded images are routed to an in-memory filesystem during the scenario.
    Storage::fake('public');

    // Build the authenticated member and supporting category required by the management workflow.
    $member = User::factory()->create();
    $category = Category::query()->create([
        'name' => 'Trail Clubs',
        'slug' => 'trail-clubs',
        'is_active' => true,
    ]);

    actingAs($member);

    // Flush cached category lookups so the component reads the fresh dataset seeded above.
    Cache::flush();

    $cover = UploadedFile::fake()->image('cover.jpg', 1200, 400);
    $icon = UploadedFile::fake()->image('icon.png', 256, 256);

    // Drive the Livewire component to create a new group via the exposed form action.
    $component = Livewire::test(Index::class)
        ->set('showCreateModal', true)
        ->set('name', 'Sunrise Hikers')
        ->set('description', 'Plan dawn adventures with fellow early risers.')
        ->set('categoryId', $category->id)
        ->set('visibility', Group::VISIBILITY_OPEN)
        ->set('location', 'Denver')
        ->set('groupRules', ['Be respectful', 'Share trail conditions'])
        ->set('coverImage', $cover)
        ->set('icon', $icon)
        ->call('createGroup')
        ->assertHasNoErrors()
        ->assertSet('showCreateModal', false);

    // Capture the newly created group so additional assertions can inspect its persisted state.
    $createdGroup = Group::query()->where('name', 'Sunrise Hikers')->first();

    $component->assertRedirect(route('group.detail', $createdGroup));

    // Confirm the persisted payload includes slugs, file paths, and stored governance rules.
    expect($createdGroup)->not->toBeNull()
        ->and($createdGroup->slug)->toBe('sunrise-hikers')
        ->and($createdGroup->rules)->toBe(['Be respectful', 'Share trail conditions']);

    // Uploaded assets should exist on the faked disk once the group has been created.
    expect(Storage::disk('public')->exists($createdGroup->cover_image))->toBeTrue()
        ->and(Storage::disk('public')->exists($createdGroup->icon))->toBeTrue();

    // The creator should be attached as an active admin member according to the component contract.
    $pivot = $createdGroup->members()->where('users.id', $member->id)->first()?->pivot;

    expect($pivot)->not->toBeNull()
        ->and($pivot->id)->not->toBeNull()
        ->and($pivot->role)->toBe('admin')
        ->and($pivot->status)->toBe('active')
        ->and($pivot->joined_at)->not->toBeNull();

    $adminRole = $createdGroup->roles()->whereRaw('LOWER(name) = ?', ['admin'])->first();

    expect($adminRole)->not->toBeNull()
        ->and($adminRole->permissions)->toContain('assign_roles');

    $assignedRoleNames = $pivot->roles()->pluck('name')->all();

    expect($assignedRoleNames)->toContain('Admin');
});
