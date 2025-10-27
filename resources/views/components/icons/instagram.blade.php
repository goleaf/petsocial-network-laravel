{{-- Instagram brand icon component supporting marketing links. --}}
@php
    $class = $attributes->get('class', 'h-5 w-5');
    $strokeWidth = $attributes->get('stroke-width', '1.5');
@endphp

<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" {{ $attributes->merge(['class' => $class, 'stroke-width' => $strokeWidth]) }}>
    <path stroke-linecap="round" stroke-linejoin="round" d="M7 3.75h10A3.25 3.25 0 0 1 20.25 7v10A3.25 3.25 0 0 1 17 20.25H7A3.25 3.25 0 0 1 3.75 17V7A3.25 3.25 0 0 1 7 3.75Z" />
    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8.25a3.75 3.75 0 1 0 3.75 3.75A3.75 3.75 0 0 0 12 8.25Z" />
    <circle cx="16.5" cy="7.5" r=".75" fill="currentColor" />
</svg>
