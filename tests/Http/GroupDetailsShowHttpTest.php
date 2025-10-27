<?php

use App\Http\Livewire\Group\Details\Show;
use App\Models\Group\Group;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * HTTP-level checks verifying middleware and Livewire bootstrapping for the group detail page.
 */
it('redirects guests to the login page when accessing group details', function (): void {
    // Persist a minimal group so the route model binding can resolve a record.
    $creator = User::factory()->create();
    $group = Group::query()->create([
        'name' => 'Guest Redirect Group',
        'slug' => sprintf('guest-redirect-group-%s', Str::uuid()),
        'description' => 'Testing guest redirection behaviour.',
        'category_id' => null,
        'visibility' => 'open',
        'creator_id' => $creator->id,
        'location' => 'Public Plaza',
        'rules' => ['Always greet new members.'],
    ]);

    // The unauthenticated request should be redirected by the auth middleware layer.
    $this->get(route('group.detail', $group))
        ->assertRedirect(route('login'));
});

it('renders the livewire component for authenticated members', function (): void {
    // Prepare a creator so the membership pivot can be satisfied before hitting the route.
    $creator = User::factory()->create();
    $group = Group::query()->create([
        'name' => 'HTTP Assertions Club',
        'slug' => sprintf('http-assertions-club-%s', Str::uuid()),
        'description' => 'Ensuring HTTP assertions remain stable.',
        'category_id' => null,
        'visibility' => 'closed',
        'creator_id' => $creator->id,
        'location' => 'Testing Grounds',
        'rules' => ['Share debugging tips.'],
    ]);

    // Membership ensures the private guard does not abort when the component mounts.
    $group->members()->attach($creator->id, ['role' => 'admin', 'status' => 'active']);

    $response = $this->actingAs($creator)->get(route('group.detail', $group));

    // Verify the request succeeded and the expected Livewire component was booted.
    $response->assertOk();
    $response->assertSeeLivewire(Show::class);
});

it('confirms the backing blade view exists for the component render', function (): void {
    // The HTTP layer should fail fast if the blade template is missing, so we assert its presence here.
    expect(view()->exists('livewire.group.details.show'))->toBeTrue();
});
