@php
    /**
     * Declares the default Tailwind dimensions for the close icon.
     *
     * @var string $class
     */
    $class = $attributes->get('class', 'h-6 w-6');

    /**
     * Lets consumers customise the close icon's stroke width.
     *
     * @var string $strokeWidth
     */
    $strokeWidth = $attributes->get('stroke-width', '1.5');
@endphp

<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" {{ $attributes->merge(['class' =>
$class, 'stroke-width' => $strokeWidth]) }}>
    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
</svg>
