{{-- Dots vertical icon component reused for menu actions. --}}
@php
    $class = $attributes->get('class', 'h-5 w-5');
    $strokeWidth = $attributes->get('stroke-width', '1.5');
@endphp

<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" {{ $attributes->merge(['class' => $class, 'stroke-width' => $strokeWidth]) }}>
    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5ZM12 12.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5ZM12 18.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5Z" />
</svg>
