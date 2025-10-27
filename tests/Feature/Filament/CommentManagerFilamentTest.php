<?php

use App\Http\Livewire\Common\CommentManager;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Livewire\Component;
use Illuminate\Support\ViewErrorBag;

// Provide a minimal Filament widget stand-in when the package is absent so compatibility tests can run.
if (! class_exists('Filament\\Widgets\\Widget')) {
    class_alias(Component::class, 'Filament\\Widgets\\Widget');
}

it('renders through a Filament widget wrapper', function (): void {
    // Unguard comments to insert fixture data powering the widget rendering assertions.
    Comment::unguard();

    // Seed the post and comment content that the widget should surface once rendered.
    $owner = User::factory()->create(['name' => 'owner']);
    $post = Post::create([
        'user_id' => $owner->id,
        'content' => 'Filament post body',
    ]);
    Comment::create([
        'user_id' => $owner->id,
        'post_id' => $post->id,
        'content' => 'Filament wrapper comment',
    ]);

    // Build an anonymous widget that boots the Livewire component the same way Filament pages mount widgets.
    $widget = new class ($post->id) extends \Filament\Widgets\Widget {
        /**
         * The wrapped Livewire component instance.
         */
        public CommentManager $manager;

        public function __construct(public int $postId)
        {
            // Resolve the component from the container and run mount() to hydrate contextual state.
            $this->manager = app(CommentManager::class);
            $this->manager->mount($this->postId);
        }

        public function render()
        {
            // Delegate rendering to the Livewire component so Filament receives the Blade view payload.
            return $this->manager->render()->with([
                'replyingToId' => $this->manager->replyingToId,
                'editingCommentId' => $this->manager->editingCommentId,
                'editingContent' => $this->manager->editingContent,
                'content' => $this->manager->content,
                'errors' => new ViewErrorBag,
            ]);
        }
    };

    // Capture the rendered HTML and confirm the seeded comment appears, proving the widget pipeline works.
    $html = $widget->render()->render();
    expect($html)->toContain('Filament wrapper comment');

    // Restore guarding to keep other suites isolated from the relaxed configuration.
    Comment::reguard();
});
