<?php

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;

/**
 * Ensures every icons component referenced in Blade templates is available and renderable.
 */
it('provides blade components for each referenced icon', function (): void {
    $viewDirectory = resource_path('views');
    $bladeFiles = File::allFiles($viewDirectory);
    $pattern = '/<x-icons\.([a-z0-9\-]+)/i';
    $referencedIcons = collect();

    foreach ($bladeFiles as $file) {
        /** @var \SplFileInfo $file */
        $contents = $file->getContents();
        preg_match_all($pattern, $contents, $matches);
        if (! empty($matches[1])) {
            $referencedIcons = $referencedIcons->merge($matches[1]);
        }
    }

    $uniqueIcons = $referencedIcons->unique()->values();

    expect($uniqueIcons)->not->toBeEmpty();

    $uniqueIcons->each(function (string $icon): void {
        $viewName = 'components.icons.'.$icon;
        expect(view()->exists($viewName))->toBeTrue("Expected icon view [{$viewName}] to exist.");
        $rendered = Blade::render('<x-icons.'.$icon.' class="h-5 w-5" stroke-width="1.5" />');
        expect($rendered)->not->toBeEmpty();
    });
});
