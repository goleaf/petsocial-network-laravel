@php
    /**
     * Provides the default Tailwind sizing for the chevron down icon.
     *
     * @var string $class
     */
    $class = $attributes->get('class', 'h-6 w-6');

    /**
     * Keeps the chevron stroke width adjustable with a default fallback.
     *
     * @var string $strokeWidth
     */
    $strokeWidth = $attributes->get('stroke-width', '1.5');
@endphp

<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" {{ $attributes->merge(['class' =>
$class, 'stroke-width' => $strokeWidth]) }}>
    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
</svg>
