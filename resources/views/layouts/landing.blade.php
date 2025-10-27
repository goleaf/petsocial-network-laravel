<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $pageTitle ?? config('app.name', 'Pets Social Network') }}</title>

        <!-- Include the project font stack and compiled assets. -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="min-h-screen font-sans antialiased bg-gradient-to-b from-slate-50 via-white to-slate-100 text-gray-900">
        <!-- The landing layout simply provides a clean canvas for Livewire content. -->
        <div class="min-h-screen flex flex-col">
            {{ $slot }}
        </div>

        @livewireScripts
        @stack('scripts')
    </body>
</html>
