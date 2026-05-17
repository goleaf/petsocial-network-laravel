<?php

use App\Http\Livewire\Content\CommentSection;
use App\Models\Post;
use App\Models\User;

it('exposes mention suggestions in a commentions compatible structure for Filament inputs', function (): void {
    // Seed a post owner and mentionable users so the component can return deterministic suggestion payloads.
    $owner = User::factory()->create(['name' => 'OwnerAlpha']);
    User::factory()->create(['name' => 'AmyAdmin']);
    User::factory()->create(['name' => 'AndrewPanel']);
    User::factory()->create(['name' => 'Beatrice']);

    $post = Post::create([
        'user_id' => $owner->id,
        'content' => 'Filament commentions compatibility check post',
    ]);

    // Mount the component as a Filament resource page would when rendering inline comments.
    $component = app(CommentSection::class);
    $component->mount($post->id);

    // Filter suggestion results by a mention fragment and validate the shape expected by drop-in Commentions fields.
    $suggestions = $component->mentionSuggestions('Am');

    expect($suggestions)->not->toBeEmpty();
    expect($suggestions[0])->toHaveKeys(['id', 'value', 'label']);
    expect(collect($suggestions)->pluck('value')->every(fn (string $value): bool => str_starts_with($value, '@')))->toBeTrue();
    expect(collect($suggestions)->pluck('label'))->toContain('AmyAdmin');
    expect(collect($suggestions)->pluck('label'))->not->toContain('Beatrice');
});
