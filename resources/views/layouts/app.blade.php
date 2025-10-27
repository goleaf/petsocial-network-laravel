<!DOCTYPE html>
<html>
<head>
    <title>{{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body class="bg-gray-100 font-sans min-h-screen">
<nav class="bg-white shadow p-4 sticky top-0 z-10">
    <div class="max-w-7xl mx-auto flex flex-col sm:flex-row justify-between items-center">
        <a href="/" class="text-xl font-bold text-gray-800 mb-3 sm:mb-0">{{ config('app.name') }}</a>
        <div class="flex flex-col sm:flex-row items-center space-y-3 sm:space-y-0 sm:space-x-4 text-center">
            <x-language-switcher />
            <a href="{{ route('dashboard') }}" class="text-gray-600 hover:text-gray-800">{{ __('common.dashboard') }}</a>
            <a href="{{ route('profile.edit') }}" class="text-gray-600 hover:text-gray-800">{{ __('common.profile') }}</a>
            <a href="{{ route('tag.search') }}" class="text-gray-600 hover:text-gray-800">{{ __('common.tags') }}</a>
            <a href="{{ route('messages') }}" class="text-gray-600 hover:text-gray-800">{{ __('common.messages') }}</a>
            <a href="{{ route('friends') }}" class="text-gray-600 hover:text-gray-800">{{ __('common.friends') }}</a>
            <a href="{{ route('followers') }}" class="text-gray-600 hover:text-gray-800">{{ __('common.followers') }}</a>
            <a href="{{ route('pets') }}" class="text-gray-600 hover:text-gray-800">{{ __('common.pets') }}</a>
            <a href="{{ route('friend.requests') }}" class="text-gray-600 hover:text-gray-800">{{ __('common.requests') }}</a>
            @if (auth()->user()->isAdmin())
                <a href="{{ route('admin.dashboard') }}" class="text-gray-600 hover:text-gray-800">{{ __('common.admin') }}</a>
            @endif
            <a href="{{ route('settings') }}" class="text-gray-600 hover:text-gray-800">{{ __('common.settings') }}</a>
            <form action="{{ route('logout') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="text-gray-600 hover:text-gray-800">{{ __('common.logout') }}</button>
            </form>
        </div>
    </div>
</nav>
<div class="max-w-7xl mx-auto p-4 flex flex-col lg:flex-row gap-4">
    <main class="flex-1">
        {{-- Livewire components can render directly into the default slot while legacy Blade views continue using the content section. --}}
        {{ $slot ?? '' }}
        @yield('content')
    </main>
    <aside class="w-full lg:w-64">
        @livewire('trending-tags')
        @livewire('common.friend.suggestions', ['entityType' => 'user', 'entityId' => auth()->id()])
    </aside>
</div>
<script>
    Echo.private('App.Models.User.' + {{ auth()->id() }})
        .notification((notification) => {
            const notifications = document.getElementById('notifications');
            notifications.innerHTML += `<p class="p-2 bg-blue-100 rounded-lg mb-2">${notification.message}</p>`;
            setTimeout(() => notifications.querySelector('p')?.remove(), 5000); // Auto-dismiss after 5s
        });
</script>
@livewireScripts
</body>
</html>
