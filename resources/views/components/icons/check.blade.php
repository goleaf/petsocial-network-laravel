@php
    /**
     * Sets the default Tailwind sizing for the checkmark icon.
     *
     * @var string $class
     */
    $class = $attributes->get('class', 'h-6 w-6');

    /**
     * Keeps the checkmark stroke width adjustable for different contexts.
     *
     * @var string $strokeWidth
     */
    $strokeWidth = $attributes->get('stroke-width', '1.5');
@endphp

<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" {{ $attributes->merge(['class' =>
$class, 'stroke-width' => $strokeWidth]) }}>
    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
</svg>
