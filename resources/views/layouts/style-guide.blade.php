<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <!-- Layout dedicated to the immersive UX/UI style guide experience. -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }} · UX/UI Style Guide</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body x-data="styleGuideShell()" :class="darkMode ? 'bg-slate-950 text-slate-100' : 'bg-slate-100 text-slate-900'" class="min-h-screen transition-colors duration-300">
    <!-- Persistent navigation bar keeps designers oriented across sections. -->
    <header class="border-b border-slate-200 dark:border-slate-800 bg-white/80 dark:bg-slate-900/80 backdrop-blur sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 py-4 flex flex-col lg:flex-row lg:items-center gap-4">
            <div class="flex items-center justify-between">
                <a href="{{ url('/') }}" class="flex items-center gap-2">
                    <x-application-logo class="h-8 w-8" />
                    <span class="text-lg font-semibold">{{ config('app.name') }} Style Guide</span>
                </a>
            </div>
            <div class="flex flex-wrap items-center gap-3 text-sm font-medium">
                <a href="#core-components" class="hover:text-indigo-600 dark:hover:text-indigo-400">Core Components</a>
                <a href="#page-patterns" class="hover:text-indigo-600 dark:hover:text-indigo-400">Page Patterns</a>
                <a href="#interaction-library" class="hover:text-indigo-600 dark:hover:text-indigo-400">Interactions</a>
                <button type="button" @click="darkMode = !darkMode" class="flex items-center gap-2 px-3 py-1.5 rounded-full border border-slate-300 dark:border-slate-700 hover:bg-slate-200 dark:hover:bg-slate-800 transition">
                    <svg x-show="!darkMode" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2m0 14v2m9-9h-2M5 12H3m15.364 6.364l-1.414-1.414M7.05 7.05 5.636 5.636m12.728 0-1.414 1.414M7.05 16.95l-1.414 1.414M12 7a5 5 0 015 5 5 5 0 01-5 5 5 5 0 01-5-5 5 5 0 015-5z" />
                    </svg>
                    <svg x-show="darkMode" x-cloak xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z" />
                    </svg>
                    <span x-text="darkMode ? 'Dark' : 'Light'"></span>
                </button>
            </div>
        </div>
    </header>
    <!-- Primary content channel renders all component documentation. -->
    <main class="max-w-7xl mx-auto px-4 py-10 space-y-12">
        @yield('content')
    </main>
    <!-- Footer anchors quick navigation aids for long-form documentation. -->
    <footer class="border-t border-slate-200 dark:border-slate-800 py-6 mt-12">
        <div class="max-w-7xl mx-auto px-4 flex flex-col sm:flex-row justify-between gap-4 text-sm">
            <p>Built for designers and engineers delivering cohesive experiences for {{ config('app.name') }}.</p>
            <div class="flex gap-4">
                <a href="#core-components" class="hover:text-indigo-600 dark:hover:text-indigo-400">Components</a>
                <a href="#page-patterns" class="hover:text-indigo-600 dark:hover:text-indigo-400">Patterns</a>
                <a href="#interaction-library" class="hover:text-indigo-600 dark:hover:text-indigo-400">Interactions</a>
            </div>
        </div>
    </footer>
    @livewireScripts
    <script>
        // Alpine helper centralises shell-level interactions such as theming.
        function styleGuideShell() {
            return {
                darkMode: window.matchMedia('(prefers-color-scheme: dark)').matches,
            };
        }
    </script>
</body>
</html>
