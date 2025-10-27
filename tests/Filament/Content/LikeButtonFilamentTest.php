<?php

use App\Http\Livewire\Content\LikeButton;
use App\Models\Post;
use App\Models\User;
use Livewire\Livewire;
use function Pest\Laravel\actingAs;

test('like button markup can be embedded inside filament panels', function (): void {
    /**
     * Create the baseline data so the component renders meaningful output.
     */
    $user = User::factory()->create();
    $post = Post::query()->create([
        'user_id' => $user->id,
        'content' => 'Filament surface rendering smoke test.',
    ]);

    /**
     * Mount the component just as Filament would when including a Livewire widget.
     */
    actingAs($user);
    $html = Livewire::test(LikeButton::class, ['postId' => $post->id])->html();

    /**
     * Confirm the rendered HTML exposes the core call-to-action copy expected inside Filament cards.
     */
    expect($html)->toContain('Like');
})->skip(! class_exists('Filament\\Support\\Testing\\TestsFilament'), 'Filament is not installed, so the integration check is skipped.');
