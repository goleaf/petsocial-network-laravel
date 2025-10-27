<?php

use App\Http\Livewire\Group\Forms\Create;
use App\Models\Group\Category;
use App\Models\Group\Group;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

/**
 * Dedicated Livewire tests for the group creation component behaviour.
 */
describe('Group create Livewire component', function () {
    it('validates required fields before attempting to create a group', function () {
        // Drive the component without providing any data to trigger validation feedback.
        Livewire::test(Create::class)
            ->call('createGroup')
            ->assertHasErrors([
                'name' => 'required',
                'description' => 'required',
                'visibility' => 'required',
                'categoryId' => 'required',
            ]);
    });

    it('generates a unique slug when another group with the same name exists', function () {
        // Provide storage fakes to accept uploaded media assets.
        Storage::fake('public');

        // Establish the supporting category and members needed for the scenario.
        $category = Category::query()->create([
            'name' => 'Adventure',
            'slug' => 'adventure',
            'description' => 'Adventurous members unite.',
            'is_active' => true,
        ]);
        $existingCreator = User::factory()->create();
        $newCreator = User::factory()->create();

        // Seed an existing group that would normally collide on slug generation.
        Group::query()->create([
            'name' => 'Trail Blazers',
            'slug' => 'trail-blazers',
            'description' => 'Original hiking pack.',
            'category_id' => $category->id,
            'visibility' => Group::VISIBILITY_OPEN,
            'location' => 'Boulder, CO',
            'rules' => [],
            'creator_id' => $existingCreator->id,
        ]);

        // Authenticate the new creator who is attempting to register a similarly named group.
        actingAs($newCreator);

        // Execute the Livewire interaction to confirm the slug increments cleanly.
        $component = Livewire::test(Create::class)
            ->set('name', 'Trail Blazers')
            ->set('description', 'Newcomers exploring the same trails with a unique twist.')
            ->set('categoryId', $category->id)
            ->set('visibility', Group::VISIBILITY_OPEN)
            ->set('coverImage', UploadedFile::fake()->image('cover.jpg'))
            ->set('icon', UploadedFile::fake()->image('icon.jpg'));

        $component->call('createGroup');

        $group = Group::query()->where('creator_id', $newCreator->id)->first();
        expect($group)->not->toBeNull();
        expect($group->slug)->toBe('trail-blazers-1');
    });
});
