<div>
    <h1 class="text-2xl font-bold mb-4">{{ __('admin.admin_panel') }}</h1>
    <p class="mb-4">{{ __('admin.welcome_admin', ['name' => auth()->user()->name]) }}</p>
    <a href="{{ route('admin.users') }}" class="text-blue-500 hover:underline">{{ __('admin.manage_users') }}</a>
    <a href="{{ route('admin.analytics') }}" class="text-blue-500 hover:underline ml-4">{{ __('admin.analytics') }}</a>
</div>
