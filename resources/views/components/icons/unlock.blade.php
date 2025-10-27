{{-- Unlock icon component representing restored access. --}}
@php
    $class = $attributes->get('class', 'h-5 w-5');
    $strokeWidth = $attributes->get('stroke-width', '1.5');
@endphp

<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" {{ $attributes->merge(['class' => $class, 'stroke-width' => $strokeWidth]) }}>
    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5V6.75a4.5 4.5 0 1 1 9 0v3.75M3.75 21.75h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H3.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
</svg>
