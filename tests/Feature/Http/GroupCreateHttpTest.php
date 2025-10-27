<?php

use App\Http\Livewire\Group\Forms\Create;
use App\Models\Group\Category;
use App\Models\Group\Group;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

/**
 * HTTP level regression coverage for the group creation flow.
 */
test('newly created groups are reachable through the HTTP detail route', function () {
    // Ensure media uploads remain isolated to the virtual filesystem during the request cycle.
    Storage::fake('public');

    // Prepare the authenticated member and category dependencies.
    $user = User::factory()->create();
    $category = Category::query()->create([
        'name' => 'HTTP Explorers',
        'slug' => 'http-explorers',
        'description' => 'Routing adventures for test coverage.',
        'is_active' => true,
    ]);
    actingAs($user);

    // Execute the Livewire action that ultimately issues an HTTP redirect to the detail page.
    $component = Livewire::test(Create::class)
        ->set('name', 'Protocol Pioneers')
        ->set('description', 'Ensuring the redirect target loads without issues.')
        ->set('categoryId', $category->id)
        ->set('visibility', Group::VISIBILITY_OPEN)
        ->set('coverImage', UploadedFile::fake()->image('cover.jpg'))
        ->set('icon', UploadedFile::fake()->image('icon.jpg'));

    $component->call('createGroup');

    $group = Group::query()->where('name', 'Protocol Pioneers')->firstOrFail();

    // Sanity check that the intended redirect route is actually registered in the HTTP layer.
    expect(Route::has('group.detail'))->toBeTrue();

    // Follow the redirect target to confirm the HTTP endpoint responds with success and renders the group name.
    get(route('group.detail', $group))
        ->assertOk()
        ->assertSee('Protocol Pioneers');
});

test('the create component blade remains registered for HTTP redirects to reuse', function () {
    // Ensure the factory resolves the blade so downstream redirects never reference a missing template.
    expect(View::exists('livewire.group.forms.create'))->toBeTrue();
});
