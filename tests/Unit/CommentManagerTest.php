<?php

namespace Tests\Unit;

use App\Http\Livewire\Common\CommentManager;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use ReflectionClass;
use Tests\TestCase;

class CommentManagerTest extends TestCase
{
    use RefreshDatabase;

    public function test_parse_mentions_returns_existing_users_only(): void
    {
        // Provision two users with mention-friendly handles so the regex can target deterministic values.
        $mentioned = User::factory()->create(['name' => 'buddy']);
        User::factory()->create(['name' => 'spectator']);

        // Access the protected parseMentions() helper through reflection to validate the extraction logic.
        $manager = new CommentManager();
        $method = (new ReflectionClass(CommentManager::class))->getMethod('parseMentions');
        $method->setAccessible(true);

        // Invoke the method with a mixture of valid and invalid mentions.
        $result = $method->invoke($manager, 'Hello @buddy and @ghost');

        // Only the existing user should appear in the resulting collection.
        $this->assertCount(1, $result);
        $this->assertTrue($result->contains('id', $mentioned->id));
    }

    public function test_clear_comments_cache_flushes_expected_keys(): void
    {
        // Prepare the component with a known post identifier so cache keys are predictable.
        $manager = new CommentManager();
        $manager->postId = 99;

        // Expect each cache segment to be purged exactly once when the helper runs.
        Cache::shouldReceive('forget')->once()->with('post_99_comments');
        Cache::shouldReceive('forget')->once()->with('post_99_comments_count');
        Cache::shouldReceive('forget')->once()->with('post_99_top_comments');

        // Call the protected helper via reflection to trigger the forget calls without Livewire plumbing.
        $method = (new ReflectionClass(CommentManager::class))->getMethod('clearCommentsCache');
        $method->setAccessible(true);
        $method->invoke($manager);
    }
}
