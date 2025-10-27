<?php

use App\Http\Controllers\Account\AccountController;
use App\Http\Controllers\Account\ProfileController;
use App\Http\Controllers\Account\TwoFactorAuthController;
use App\Http\Controllers\Social\FollowController;
use App\Http\Controllers\Social\UnifiedFriendshipController;
use App\Http\Livewire\Account\Analytics as AccountAnalytics;
use App\Http\Livewire\Admin;
use App\Http\Livewire\Common;
use App\Http\Livewire\Group;
use App\Http\Livewire\Messages;
use App\Http\Livewire\Pet;
use App\Http\Livewire\TagSearch;
use App\Http\Livewire\UserSettings;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');
// UX style guide centralises canonical component demonstrations for designers and engineers.
Route::middleware('auth')->group(function () {
    Route::view('/ux/style-guide', 'ux.style-guide')->name('ux.style-guide');
});
Route::get('/language/{locale}', [\App\Http\Controllers\LanguageController::class, 'switchLang'])->name('language.switch');
Route::view('/dashboard', 'dashboard')->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::controller(ProfileController::class)->group(function () {
        Route::get('/profile', 'edit')->name('profile.edit');
        Route::patch('/profile', 'update')->name('profile.update');
        Route::delete('/profile', 'destroy')->name('profile.destroy');
    });

    Route::prefix('account')->name('account.')->controller(AccountController::class)->group(function () {
        Route::post('/deactivate', 'deactivate')->name('deactivate');
        Route::post('/delete', 'delete')->name('delete');
        Route::post('/password', 'updatePassword')->name('password.update');
    });

    Route::prefix('two-factor')->name('two-factor.')->controller(TwoFactorAuthController::class)->group(function () {
        Route::get('/enable', 'enable')->name('enable');
        Route::post('/confirm', 'confirm')->name('confirm');
        Route::post('/disable', 'disable')->name('disable');
        Route::get('/challenge', 'challenge')->name('challenge');
        Route::post('/verify', 'verify')->name('verify');
    });

    Route::get('/profile/{user}', function (User $user) {
        if (($user->profile_visibility === 'private' || ($user->profile_visibility === 'friends' && ! $user->friends->contains(auth()->id()))) && $user->id !== auth()->id()) {
            abort(403, 'Profile access restricted.');
        }

        return view('profile', compact('user'));
    })->name('profile');

    Route::get('/tags', TagSearch::class)->name('tag.search');
    Route::get('/search', Common\UnifiedSearch::class)->name('search');
    Route::get('/messages', Messages::class)->name('messages');
    Route::get('/settings', UserSettings::class)->name('settings');
    Route::get('/notifications', fn () => app(Common\NotificationCenter::class, ['entityType' => 'user', 'entityId' => auth()->id()]))->name('notifications');
    Route::get('/posts', fn () => app(Common\PostManager::class, ['entityType' => 'user', 'entityId' => auth()->id()]))->name('posts');
    Route::get('/account/analytics', AccountAnalytics::class)->middleware('can:analytics.view')->name('account.analytics');
    Route::get('/activity', function () {
        $entityType = request('entity_type', 'user');
        $entityId = request('entity_id', auth()->id());

        if ($entityType === 'user') {
            $targetUser = User::findOrFail($entityId);
            $viewer = auth()->user();

            if (! $targetUser->canViewPrivacySection($viewer, 'activity') && $viewer->id !== $targetUser->id && ! $viewer->isAdmin()) {
                abort(403, __('profile.activity_private'));
            }
        }

        return app(Common\Friend\ActivityLog::class, ['entityType' => $entityType, 'entityId' => $entityId]);
    })->name('activity');

    $commonComponents = [
        'friend-requests' => ['Common\FriendsList', ['entityType' => 'user', 'entityId' => 'auth()->id()', 'initialFilter' => 'pending']],
        'friends' => ['Common\FriendsList', ['entityType' => 'user', 'entityId' => 'auth()->id()']],
        'followers' => Common\Follow\FollowList::class,
    ];

    foreach ($commonComponents as $route => $component) {
        Route::get("/$route", is_array($component)
            ? fn () => app($component[0], array_map(fn ($v) => is_callable($v) ? $v() : $v, $component[1]))
            : $component)->name(str_replace('-', '.', $route));
    }

    Route::prefix('friends')->name('friend.')->group(function () {
        $friendComponents = [
            'dashboard' => 'Common\Friend\Hub',
            'export' => 'Common\Friend\Export',
            'finder' => 'Common\Friend\Finder',
            'analytics' => 'Common\Friend\Analytics',
        ];

        foreach ($friendComponents as $route => $component) {
            Route::get("/$route", is_string($component)
                ? fn () => app($component, ['entityType' => 'user', 'entityId' => auth()->id()])
                : $component)->name($route);
        }
    });

    Route::prefix('friendships')->name('friendships.')->controller(UnifiedFriendshipController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/{user}/request', 'sendRequest')->name('request');
        Route::post('/accept/{friendship}', 'acceptRequest')->name('accept');
        Route::post('/decline/{friendship}', 'declineRequest')->name('decline');
        Route::delete('/{user}', 'removeFriend')->name('remove');
        Route::post('/{user}/categorize', 'categorize')->name('categorize');
        Route::post('/{user}/block', 'blockUser')->name('block');
        Route::post('/{user}/unblock', 'unblockUser')->name('unblock');
        Route::get('/blocked', 'blockedUsers')->name('blocked');
    });

    Route::prefix('follows')->name('follows.')->controller(FollowController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/{user}', 'follow')->name('follow');
        Route::delete('/{user}', 'unfollow')->name('unfollow');
        Route::post('/{user}/notifications', 'toggleNotifications')->name('toggle-notifications');
        Route::get('/recommendations', 'recommendations')->name('recommendations');
    });

    Route::controller(FollowController::class)->group(function () {
        Route::get('/users/{user}/followers', 'followers')->name('users.followers');
        Route::get('/users/{user}/following', 'following')->name('users.following');
    });

    Route::get('/pets', Pet\PetManagement::class)->name('pets');

    Route::prefix('pets')->name('pet.')->group(function () {
        $petComponents = [
            'friends' => 'Common\Friend\List',
            'dashboard' => 'Common\Friend\Hub',
            'finder' => 'Common\Friend\Finder',
            'analytics' => 'Common\Friend\Analytics',
            'notifications' => 'Common\NotificationCenter',
            'posts' => 'Common\PostManager',
        ];

        foreach ($petComponents as $route => $component) {
            Route::get("/$route/{petId}", fn ($petId) => app($component, ['entityType' => 'pet', 'entityId' => $petId]))->name($route);
        }

        // Dedicated page for owners to maintain private medical records.
        Route::get('/medical-records/{pet}', Pet\MedicalRecords::class)->name('medical-records');
        Route::get('/{pet}', Pet\PetProfile::class)->name('profile');
    });

    Route::prefix('groups')->name('group.')->group(function () {
        Route::get('/', Group\Management\Index::class)->name('index');
        Route::get('/{group}', Group\Details\Show::class)->name('detail');
    });
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', Admin\Dashboard::class)->name('dashboard');
    Route::get('/users', Admin\ManageUsers::class)->name('users');
    Route::get('/analytics', Admin\Analytics::class)->name('analytics');
    Route::get('/reports', fn () => response()->noContent())->name('reports');
    Route::get('/settings', fn () => response()->noContent())->name('settings');
});

require __DIR__.'/auth.php';
