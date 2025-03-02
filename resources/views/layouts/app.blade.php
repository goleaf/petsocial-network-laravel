<!DOCTYPE html>
<html>
<head>
    <title>{{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body class="bg-gray-100 font-sans min-h-screen">
<nav class="navbar">
    <div class="container">
        <a href="/" class="navbar-brand">Pet Social Network</a>
        <ul class="navbar-nav">
            <li class="nav-item"><a href="/friends" class="nav-link">Friends</a></li>
            <li class="nav-item"><a href="/pets" class="nav-link">Pets</a></li>
            <li class="nav-item"><a href="/posts" class="nav-link">Posts</a></li>
        </ul>
        <div class="language-switcher">
            <a href="?lang=en" class="lang-link">EN</a>
            <a href="?lang=ru" class="lang-link">RU</a>
            <a href="?lang=lt" class="lang-link">LT</a>
        </div>
    </div>
</nav>
<div class="max-w-7xl mx-auto p-4 flex flex-col lg:flex-row gap-4">
    <main class="flex-1">
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
