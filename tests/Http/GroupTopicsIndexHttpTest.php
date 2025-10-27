<?php

use App\Http\Livewire\Group\Topics\Index;
use App\Models\Group\Group;
use App\Models\User;
use Illuminate\Support\Facades\Route;

it('returns an ok response when the topics component is resolved through an HTTP route', function () {
    // Create the authenticated visitor who will request the topics page.
    $member = User::factory()->create();

    // Persist a group instance with a deterministic slug for routing.
    $group = Group::create([
        'name' => 'HTTP Routed Group',
        'slug' => 'http-routed-group',
        'description' => 'Group leveraged for HTTP integration tests.',
        'visibility' => 'open',
        'creator_id' => $member->id,
        'is_active' => true,
    ]);

    // Register the member for completeness even though the component does not enforce access checks yet.
    $group->members()->attach($member->id, [
        'role' => 'member',
        'status' => 'active',
        'joined_at' => now(),
    ]);

    // Define a temporary route that proxies directly to the Livewire component.
    Route::middleware('web')->get('/preview/groups/{group:slug}/topics', Index::class)->name('preview.group.topics');

    // Authenticate and issue the HTTP GET request to the freshly registered endpoint.
    $this->actingAs($member);
    $response = $this->get(route('preview.group.topics', $group));

    // Ensure the response resolves successfully so embedding the component in a page remains safe.
    $response->assertOk();
});
