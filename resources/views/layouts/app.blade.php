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
            <a href="{{ route('dashboard') }}" class="text-gray-600 hover:text-gray-800">Dashboard</a>
            <a href="{{ route('profile.edit') }}" class="text-gray-600 hover:text-gray-800">Profile</a>
            <a href="{{ route('tag.search') }}" class="text-gray-600 hover:text-gray-800">Tags</a>
            <a href="{{ route('messages') }}" class="text-gray-600 hover:text-gray-800">Messages</a>
            <a href="{{ route('friends') }}" class="text-gray-600 hover:text-gray-800">Friends</a>
            <a href="{{ route('followers') }}" class="text-gray-600 hover:text-gray-800">Followers</a>
            <a href="{{ route('pets') }}" class="text-gray-600 hover:text-gray-800">Pets</a>
            <a href="{{ route('friend.requests') }}" class="text-gray-600 hover:text-gray-800">Requests</a>
            @if (auth()->user()->isAdmin())
                <a href="{{ route('admin.dashboard') }}" class="text-gray-600 hover:text-gray-800">Admin</a>
            @endif
            <a href="{{ route('settings') }}" class="text-gray-600 hover:text-gray-800">Settings</a>
            <form action="{{ route('logout') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="text-gray-600 hover:text-gray-800">Logout</button>
            </form>
        </div>
    </div>
</nav>
<div class="max-w-7xl mx-auto p-4 flex flex-col lg:flex-row gap-4">
    <main class="flex-1">
        @yield('content')
    </main>
    <aside class="w-full lg:w-64">
        @livewire('trending-tags')
        @livewire('friend-suggestions')
    </aside>
</div>
@livewireScripts
</body>
</html>
