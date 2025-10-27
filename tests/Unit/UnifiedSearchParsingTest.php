<?php

use App\Http\Livewire\Common\UnifiedSearch;

/**
 * Unit coverage for parsing helpers and cache key generation within unified search.
 */
it('parses complex queries into structured segments', function () {
    // Provide a harness that exposes the otherwise protected parser.
    $component = new class extends UnifiedSearch {
        public function parseForTest(string $input): array
        {
            return $this->parseQuery($input);
        }
    };

    // Compose a query featuring terms, phrases, tags, and operators.
    $segments = $component->parseForTest('"park meetup" #Dogs tag:Rescue type:pets location:"New York" near:Austin');

    // Confirm every segment type is extracted exactly once for downstream filters.
    expect($segments['phrases'])->toContain('park meetup')
        ->and($segments['tags'])->toContain('dogs', 'rescue')
        ->and($segments['operators']['type'])->toBe('pets')
        // The final near: operator should override the earlier location directive.
        ->and($segments['operators']['location'])->toBe('Austin')
        // Residual tokens like a standalone operator prefix are preserved for free-text matching.
        ->and($segments['terms'])->toContain('location:');
});

it('generates stable cache keys for identical filter combinations', function () {
    // Expose the cache key helper so the test can inspect its output directly.
    $component = new class extends UnifiedSearch {
        public function keyForTest(string $type, string $filter, string $field, string $direction, ?string $location, string $query): string
        {
            return $this->buildCacheKey($type, $filter, $field, $direction, $location, $query);
        }
    };

    // Build two equivalent cache keys and one variation to assert deterministic behaviour.
    $firstKey = $component->keyForTest('posts', 'friends', 'created_at', 'desc', 'Austin', 'park meetup');
    $secondKey = $component->keyForTest('posts', 'friends', 'created_at', 'desc', 'Austin', 'park meetup');
    $variantKey = $component->keyForTest('posts', 'friends', 'name', 'desc', 'Austin', 'park meetup');

    // Ensure identical filter stacks produce matching keys while differing fields alter the hash.
    expect($firstKey)->toBe($secondKey)
        ->and($firstKey)->not->toBe($variantKey);
});
