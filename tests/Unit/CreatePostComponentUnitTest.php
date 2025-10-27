<?php

use App\Http\Livewire\Content\CreatePost;

/**
 * Unit tests for isolated helper logic within the CreatePost component.
 */
it('flags invalid links, repetitive content, and missing tags', function () {
    // Extend the component inline to surface the protected validation helper for testing.
    $component = new class extends CreatePost {
        public function validateFormat(string $text): void
        {
            $this->validateContentFormat($text);
        }
    };

    // Compose a body that includes an invalid URL and repetitive segments without tags.
    $component->tags = '';
    $invalidUrl = 'http://bad_url';
    $repeating = str_repeat('Repeated content block. ', 3);
    $longTail = str_repeat('Additional context to exceed the tagging threshold. ', 5);
    $body = "Check this {$invalidUrl} {$repeating}{$longTail}";

    // Invoke the validator to populate warning messages on the component instance.
    $component->validateFormat($body);

    // Confirm each guardrail surfaced a descriptive warning for the author.
    expect($component->contentWarnings)
        ->toContain("URL appears to be invalid: {$invalidUrl}")
        ->and($component->contentWarnings)
        ->toContain('Content appears to be repetitive')
        ->and($component->contentWarnings)
        ->toContain('Consider adding tags to help categorize your post');
});
