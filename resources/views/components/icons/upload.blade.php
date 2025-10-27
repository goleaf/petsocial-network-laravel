@php
    /**
     * Provides default Tailwind sizing for the upload icon.
     *
     * @var string $class
     */
    $class = $attributes->get('class', 'h-6 w-6');

    /**
     * Keeps the upload icon stroke width flexible with a default fallback.
     *
     * @var string $strokeWidth
     */
    $strokeWidth = $attributes->get('stroke-width', '1.5');
@endphp

<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" {{ $attributes->merge(['class' =>
$class, 'stroke-width' => $strokeWidth]) }}>
    <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
</svg>
