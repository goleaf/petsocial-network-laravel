<?php

namespace Tests\Unit;

use App\Http\Livewire\Common\CommentManager;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
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

    public function test_render_returns_expected_view_with_comment_data(): void
    {
        // Allow direct creation of comments so the render helper has content to supply the Blade view.
        Comment::unguard();

        // Seed the owner, post, and a single comment to populate the Livewire payload.
        $owner = User::factory()->create(['name' => 'owner']);
        $post = Post::create([
            'user_id' => $owner->id,
            'content' => 'Render pipeline post',
        ]);
        Comment::create([
            'user_id' => $owner->id,
            'post_id' => $post->id,
            'content' => 'Rendered directly from the component',
        ]);

        // Hydrate the Livewire component through mount() so subsequent render() calls mirror runtime behaviour.
        $manager = app(CommentManager::class);
        $manager->mount($post->id);

        // Invoke render() and confirm the resulting payload contains the expected view and data bindings.
        $view = $manager->render();
        $this->assertInstanceOf(View::class, $view);
        $this->assertSame('livewire.common.comment-manager', $view->getName());
        $data = $view->getData();
        $this->assertArrayHasKey('comments', $data);
        $this->assertArrayHasKey('commentsCount', $data);
        $this->assertSame(1, $data['commentsCount']);

        // Restore guarding so later tests operate with the standard mass-assignment rules.
        Comment::reguard();
    }
}
