<?php

use App\Http\Livewire\Common\PostManager;
use App\Models\Pet;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Symfony\Component\HttpKernel\Exception\HttpException;

uses(RefreshDatabase::class);

it('prevents viewing private user feeds when the visitor lacks access', function (): void {
    // Create an account with a private profile and a second user attempting to view the feed.
    $owner = User::factory()->create([
        'profile_visibility' => 'private',
        'privacy_settings' => User::PRIVACY_DEFAULTS,
    ]);

    $viewer = User::factory()->create([
        'profile_visibility' => 'public',
        'privacy_settings' => User::PRIVACY_DEFAULTS,
    ]);

    $this->actingAs($viewer);

    // Attempting to mount the component for the private user should raise an authorization error.
    expect(fn () => Livewire::test(PostManager::class, [
        'entityType' => 'user',
        'entityId' => $owner->id,
    ]))->toThrow(HttpException::class);
});

it('reschedules owned posts and refreshes the composer state', function (): void {
    // Authenticate as the author to edit an existing scheduled post.
    $author = User::factory()->create([
        'profile_visibility' => 'public',
        'privacy_settings' => User::PRIVACY_DEFAULTS,
    ]);

    $this->actingAs($author);

    $post = Post::create([
        'user_id' => $author->id,
        'content' => 'Original content',
        'scheduled_for' => now()->addDay(),
    ]);

    $newSchedule = Carbon::now()->addDays(2)->setSeconds(0);

    // Drive the edit workflow, enabling scheduling and updating the publish time.
    Livewire::test(PostManager::class, [
        'entityType' => 'user',
        'entityId' => $author->id,
    ])
        ->call('edit', $post->id)
        ->set('editingContent', 'Updated content with schedule')
        ->set('editingSchedulePost', true)
        ->set('editingScheduledFor', $newSchedule->format('Y-m-d\TH:i'))
        ->call('updatePost')
        ->assertHasNoErrors()
        ->assertDispatched('postUpdated');

    $post->refresh();

    // Ensure the new schedule was persisted and the flash message includes the formatted timestamp.
    expect($post->scheduled_for?->equalTo($newSchedule))->toBeTrue()
        ->and($post->content)->toBe('Updated content with schedule')
        ->and(session('message'))->toContain($newSchedule->format('M j, Y g:i A'));
});

it('renders the dedicated post manager blade view with the owners pets', function (): void {
    // Prepare an account with a pet so the view data can be asserted after rendering.
    $member = User::factory()->create([
        'profile_visibility' => 'public',
        'privacy_settings' => User::PRIVACY_DEFAULTS,
    ]);

    $pet = Pet::factory()->create([
        'user_id' => $member->id,
        'visibility' => 'public',
    ]);

    $this->actingAs($member);

    // Render the component and verify it targets the expected Blade view with pet context.
    Livewire::test(PostManager::class, [
        'entityType' => 'user',
        'entityId' => $member->id,
    ])
        ->assertViewIs('livewire.common.post-manager')
        ->assertViewHas('userPets', function ($pets) use ($pet) {
            // Confirm the authenticated member's pet is available to the template consumers.
            return $pets->contains('id', $pet->id);
        })
        ->assertViewHas('posts');
});
