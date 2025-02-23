<div>
    <h1>Admin Panel</h1>
    <p>Welcome, {{ auth()->user()->name }}. You have admin privileges.</p>
    <a href="{{ route('admin.users') }}">Manage Users</a>
</div>
