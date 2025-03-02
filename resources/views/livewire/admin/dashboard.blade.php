<div>
    <h1 class="text-2xl font-bold mb-4">Admin Panel</h1>
    <p class="mb-4">Welcome, {{ auth()->user()->name }}. You have admin privileges.</p>
    <a href="{{ route('admin.users') }}" class="text-blue-500 hover:underline">Manage Users</a>
    <a href="{{ route('admin.analytics') }}" class="text-blue-500 hover:underline ml-4">Analytics</a>
</div>
