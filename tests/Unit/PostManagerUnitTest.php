<?php

use App\Http\Livewire\Common\PostManager;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

class TestPostManagerHarness extends PostManager
{
    /**
     * Helper method to expose the protected attachTags behaviour for assertions.
     */
    public function exposeAttachTags(Post $post): void
    {
        $this->attachTags($post);
    }

    /**
     * Helper method to surface the mention parsing logic for isolated unit testing.
     */
    public function exposeParseMentions(string $content)
    {
        return $this->parseMentions($content);
    }

    /**
     * Helper method to reveal the cache clearing routine.
     */
    public function exposeClearPostsCache(): void
    {
        $this->clearPostsCache();
    }
}

it('parses mentions into a collection of targeted users', function (): void {
    // Seed users whose names appear in the post content mentions.
    $author = User::factory()->create([
        'name' => 'Author',
        'profile_visibility' => 'public',
        'privacy_settings' => User::PRIVACY_DEFAULTS,
    ]);

    $buddy = User::factory()->create(['name' => 'Buddy']);
    $alex = User::factory()->create(['name' => 'Alex']);

    $harness = new TestPostManagerHarness();

    // Execute the mention parsing logic using sample content.
    $results = $harness->exposeParseMentions('Chatting with @Buddy and @Alex later.');

    expect($results->pluck('id')->sort()->values()->all())->toEqualCanonicalizing([
        $buddy->id,
        $alex->id,
    ]);
    expect($results->contains($author))->toBeFalse();
});

it('attaches normalised tags to the provided post record', function (): void {
    // Create a user and base post that will receive the generated tags.
    $author = User::factory()->create([
        'profile_visibility' => 'public',
        'privacy_settings' => User::PRIVACY_DEFAULTS,
    ]);

    $post = Post::create([
        'user_id' => $author->id,
        'content' => 'Testing tag attachments.',
    ]);

    $harness = new TestPostManagerHarness();
    $harness->tags = 'Fun, Pets';

    // Drive the protected attachTags workflow via the harness helper.
    $harness->exposeAttachTags($post);

    expect($post->tags()->pluck('name')->all())->toEqualCanonicalizing(['fun', 'pets']);
});

it('clears all cached feed variations for the current entity', function (): void {
    // Cache fake datasets for each filter slot to verify the forget logic.
    $harness = new TestPostManagerHarness();
    $harness->entityType = 'user';
    $harness->entityId = 42;

    Cache::put('user_42_posts_all', 'cached', now()->addMinutes(5));
    Cache::put('user_42_posts_user', 'cached', now()->addMinutes(5));
    Cache::put('user_42_posts_friends', 'cached', now()->addMinutes(5));
    Cache::put('user_42_posts_pets', 'cached', now()->addMinutes(5));

    // Invoke the cache clearing helper and validate every key is removed.
    $harness->exposeClearPostsCache();

    expect(Cache::has('user_42_posts_all'))->toBeFalse()
        ->and(Cache::has('user_42_posts_user'))->toBeFalse()
        ->and(Cache::has('user_42_posts_friends'))->toBeFalse()
        ->and(Cache::has('user_42_posts_pets'))->toBeFalse();
});
