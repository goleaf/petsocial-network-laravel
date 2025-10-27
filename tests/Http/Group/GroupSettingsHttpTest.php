<?php

use App\Http\Livewire\Group\Settings\Index as GroupSettingsIndex;
use App\Models\Group\Category;
use App\Models\Group\Group;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/**
 * HTTP level coverage for integrating the group settings component with routes.
 */
it('processes an HTTP post request through the Livewire component', function () {
    // Register a lightweight route that proxies the payload through the component just like Livewire would.
    Route::middleware('web')->post('/groups/{group}/settings', function (Request $request, Group $group) {
        $component = app(GroupSettingsIndex::class);
        $component->mount($group);

        // Fill the public properties from the request input to mimic Livewire's hydration layer.
        $component->visibility = $request->input('visibility');
        $component->categoryId = $request->input('category_id');

        $component->updateSettings();

        return redirect()->back();
    })->name('groups.settings.save');

    $category = Category::create([
        'name' => 'Playdates',
        'slug' => 'playdates',
    ]);

    $owner = User::factory()->create();

    $group = Group::create([
        'name' => 'Weekend Walkers',
        'slug' => 'weekend-walkers',
        'visibility' => Group::VISIBILITY_OPEN,
        'category_id' => $category->id,
        'creator_id' => $owner->id,
    ]);

    // Ensure the owner is authenticated so the session flash bag is available.
    $response = $this->actingAs($owner)
        ->from('/previous')
        ->post(sprintf('/groups/%s/settings', $group->getRouteKey()), [
            'visibility' => Group::VISIBILITY_SECRET,
            'category_id' => $category->id,
        ]);

    $response->assertRedirect('/previous');
    $response->assertSessionHas('message', 'Group settings updated successfully!');

    $group->refresh();

    expect($group->visibility)->toBe(Group::VISIBILITY_SECRET);
});
