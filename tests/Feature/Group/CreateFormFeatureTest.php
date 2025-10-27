<?php

use App\Http\Livewire\Group\Forms\Create;
use App\Models\Group\Category;
use App\Models\Group\Group;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

use Illuminate\Support\Collection;

use function Pest\Laravel\actingAs;

/**
 * Feature level coverage for the group creation Livewire form.
 */
describe('Group creation form feature flow', function () {
    it('allows an authenticated user to create a fully configured group', function () {
        // Freeze the clock so we can assert the recorded joined timestamp precisely.
        Carbon::setTestNow('2025-05-01 12:00:00');

        // Provide a storage fake to capture uploaded media without touching the real filesystem.
        Storage::fake('public');

        // Seed an active category to satisfy the component requirement.
        $category = Category::query()->create([
            'name' => 'Outdoor Adventures',
            'slug' => 'outdoor-adventures',
            'description' => 'Trail focused meetups for energetic pets.',
            'is_active' => true,
        ]);

        // Authenticate a member who will become the creator and automatic admin.
        $creator = User::factory()->create();
        actingAs($creator);

        // Prepare the images that will be uploaded through the Livewire component.
        $coverUpload = UploadedFile::fake()->image('cover.jpg', 1200, 630);
        $iconUpload = UploadedFile::fake()->image('icon.png', 256, 256);

        // Exercise the Livewire component end-to-end to persist the new group record.
        $component = Livewire::test(Create::class)
            ->set('name', 'Trail Blazers')
            ->set('description', 'Weekly hikes and adventures tailored for playful pups and their humans.')
            ->set('visibility', Group::VISIBILITY_CLOSED)
            ->set('categoryId', $category->id)
            ->set('location', 'Denver, CO')
            ->set('coverImage', $coverUpload)
            ->set('icon', $iconUpload);

        $component->call('createGroup');

        // Retrieve the newly created group to run database level assertions.
        $group = Group::query()->where('name', 'Trail Blazers')->first();
        expect($group)->not->toBeNull();
        expect($group->slug)->toBe('trail-blazers');
        expect($group->visibility)->toBe(Group::VISIBILITY_CLOSED);
        expect($group->location)->toBe('Denver, CO');

        // Ensure uploaded images were persisted to the expected public disk locations.
        expect(Storage::disk('public')->exists($group->cover_image))->toBeTrue();
        expect(Storage::disk('public')->exists($group->icon))->toBeTrue();

        // Validate that the creator has been attached as an active administrator.
        $membership = DB::table('group_members')
            ->where('group_id', $group->id)
            ->where('user_id', $creator->id)
            ->first();

        expect($membership)->not->toBeNull();
        expect($membership->role)->toBe('admin');
        expect($membership->status)->toBe('active');
        expect(Carbon::parse($membership->joined_at)->toDateTimeString())->toBe('2025-05-01 12:00:00');

        // Confirm the component flashed the success message and issued the redirect to the detail route.
        expect(session()->get('message'))->toBe('Group created successfully!');
        $component->assertRedirect(route('group.detail', $group));

        // Release the frozen clock to avoid leaking time travel into other scenarios.
        Carbon::setTestNow();
    });

    it('exposes the creation blade with only active categories for selection', function () {
        // Flush cached datasets to guarantee the component fetches the latest categories from the database.
        Cache::flush();

        // Persist both active and inactive categories to validate the render-time filtering behaviour.
        $activeCategory = Category::query()->create([
            'name' => 'Scenic Explorers',
            'slug' => 'scenic-explorers',
            'description' => 'Featured in the creation dropdown.',
            'display_order' => 1,
            'is_active' => true,
        ]);
        $inactiveCategory = Category::query()->create([
            'name' => 'Retired Groups',
            'slug' => 'retired-groups',
            'description' => 'Should not appear for new group creation.',
            'display_order' => 2,
            'is_active' => false,
        ]);

        // Boot the Livewire component to inspect the rendered blade and the categories dataset.
        Livewire::test(Create::class)
            ->assertViewIs('livewire.group.forms.create')
            ->assertViewHas('categories', function (Collection $categories) use ($activeCategory, $inactiveCategory): bool {
                // Ensure only active categories are surfaced to the UI.
                return $categories->pluck('id')->contains($activeCategory->id)
                    && ! $categories->pluck('id')->contains($inactiveCategory->id);
            });
    });
});
