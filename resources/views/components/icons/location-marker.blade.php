{{-- Minimal placeholder used in tests to satisfy the location marker icon dependency. --}}
<svg {{ $attributes->merge(['viewBox' => '0 0 24 24', 'fill' => 'none', 'stroke' => 'currentColor']) }}>
    {{-- Simple circle indicator to avoid pulling in the full asset during automated testing. --}}
    <circle cx="12" cy="10" r="3" stroke-width="1.5"></circle>
    <path d="M12 22c5-6 7-9 7-12a7 7 0 1 0-14 0c0 3 2 6 7 12z" stroke-width="1.5"></path>
</svg>
