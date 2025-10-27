@php
    /**
     * Delivers the default Tailwind sizing for the clock icon.
     *
     * @var string $class
     */
    $class = $attributes->get('class', 'h-6 w-6');

    /**
     * Supports overriding the stroke width on the clock icon.
     *
     * @var string $strokeWidth
     */
    $strokeWidth = $attributes->get('stroke-width', '1.5');
@endphp

<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" {{ $attributes->merge(['class' =>
$class, 'stroke-width' => $strokeWidth]) }}>
    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
</svg>
