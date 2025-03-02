<?php

use App\Http\Controllers\Account\{ProfileController, AccountController, TwoFactorAuthController};
use App\Http\Controllers\Social\{UnifiedFriendshipController, FollowController};
use App\Http\Livewire\{Messages, TagSearch, UserSettings, Admin, Common, Group, Pet, Social};
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');
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
        if (($user->profile_visibility === 'private' || ($user->profile_visibility === 'friends' && !$user->friends->contains(auth()->id()))) && $user->id !== auth()->id()) {
            abort(403, 'Profile access restricted.');
        }
        return view('profile', compact('user'));
    })->name('profile');

    Route::get('/tags', TagSearch::class)->name('tag.search');
    Route::get('/search', Common\UnifiedSearch::class)->name('search');
    Route::get('/messages', Messages::class)->name('messages');
    Route::get('/settings', UserSettings::class)->name('settings');
    Route::get('/notifications', fn() => app(Common\NotificationCenter::class, ['entityType' => 'user', 'entityId' => auth()->id()]))->name('notifications');
    Route::get('/posts', fn() => app(Common\PostManager::class, ['entityType' => 'user', 'entityId' => auth()->id()]))->name('posts');
    
    $commonComponents = [
        'friend-requests' => ['Common\FriendsList', ['entityType' => 'user', 'entityId' => 'auth()->id()', 'initialFilter' => 'pending']],
        'friends' => ['Common\FriendsList', ['entityType' => 'user', 'entityId' => 'auth()->id()']],
        'followers' => Social\Follow\FollowList::class,
    ];

    foreach ($commonComponents as $route => $component) {
        Route::get("/$route", is_array($component) ? 
            fn() => app($component[0], array_map(fn($v) => is_callable($v) ? $v() : $v, $component[1])) : 
            $component
        )->name(str_replace('-', '.', $route));
    }
    
    Route::prefix('friends')->name('friend.')->group(function () {
        $friendComponents = [
            'activity' => 'Common\ActivityLog',
            'dashboard' => 'Common\FriendHub',
            'export' => Social\Friend\Export::class,
            'finder' => 'Common\FriendFinder',
            'analytics' => 'Common\FriendAnalytics',
        ];

        foreach ($friendComponents as $route => $component) {
            Route::get("/$route", is_string($component) ? 
                fn() => app($component, ['entityType' => 'user', 'entityId' => auth()->id()]) : 
                $component
            )->name($route);
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
            'friends' => 'Common\FriendsList',
            'activity' => 'Common\ActivityLog',
            'dashboard' => 'Common\FriendHub',
            'finder' => 'Common\FriendFinder',
            'analytics' => 'Common\FriendAnalytics',
            'notifications' => 'Common\NotificationCenter',
            'posts' => 'Common\PostManager',
        ];

        foreach ($petComponents as $route => $component) {
            Route::get("/$route/{petId}", fn($petId) => app($component, ['entityType' => 'pet', 'entityId' => $petId]))->name($route);
        }
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
});

require __DIR__.'/auth.php';
