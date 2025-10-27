{{-- Facebook brand icon component for social links. --}}
@php
    $class = $attributes->get('class', 'h-5 w-5');
@endphp

<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" {{ $attributes->merge(['class' => $class]) }}>
    <path d="M22 12a10 10 0 1 0-11.563 9.875v-7h-2.594V12h2.594V9.797c0-2.563 1.523-3.969 3.856-3.969 1.117 0 2.285.199 2.285.199v2.5h-1.287c-1.268 0-1.664.788-1.664 1.596V12h2.832l-.453 2.875h-2.379v7A10.001 10.001 0 0 0 22 12Z" />
</svg>
