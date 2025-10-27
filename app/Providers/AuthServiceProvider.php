<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        Gate::define('admin.access', static fn (User $user): bool => $user->hasPermission('admin.access'));
        Gate::define('moderation.manage', static fn (User $user): bool => $user->hasPermission('moderation.*'));
        Gate::define('analytics.view', static fn (User $user): bool => $user->hasPermission('analytics.view') || $user->hasPermission('analytics.view_self'));
    }
}
