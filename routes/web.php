<?php

use App\Http\Controllers\Account\AccountController;
use App\Http\Controllers\Account\ProfileController;
use App\Http\Controllers\Account\TwoFactorAuthController;
use App\Http\Controllers\Social\FollowController;
use App\Http\Controllers\Social\UnifiedFriendshipController;
use App\Http\Livewire\Account\Analytics as AccountAnalytics;
use App\Http\Livewire\Landing\HomePage;
use App\Http\Livewire\Admin;
use App\Http\Livewire\Common;
use App\Http\Livewire\Group;
use App\Http\Livewire\Messages;
use App\Http\Livewire\Pet;
use App\Http\Livewire\TagSearch;
use App\Http\Livewire\UserSettings;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;

Route::get('/', HomePage::class)->name('landing');
Route::get('/language/{locale}', [\App\Http\Controllers\LanguageController::class, 'switchLang'])->name('language.switch');
Route::view('/dashboard', 'dashboard')->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    // UX style guide centralises canonical component demonstrations for designers and engineers.
    Route::view('/ux/style-guide', 'ux.style-guide')->name('ux.style-guide');
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
    // Helper closure used by multiple routes to mount Livewire components with consistent responses.
    $renderLivewireComponent = static function (string $componentClass, array $parameters = []) {
        $resolvedParameters = [];

        foreach ($parameters as $key => $value) {
            $resolvedParameters[$key] = $value instanceof \Closure ? $value() : $value;
        }

        return response(Livewire::mount($componentClass, $resolvedParameters));
    };

    Route::get('/notifications', static fn() => $renderLivewireComponent(
        Common\NotificationCenter::class,
        [
            'entityType' => 'user',
            'entityId' => static fn(): int => auth()->id(),
        ]
    ))->name('notifications');

    Route::get('/posts', static fn() => $renderLivewireComponent(
        Common\PostManager::class,
        [
            'entityType' => 'user',
            'entityId' => static fn(): int => auth()->id(),
        ]
    ))->name('posts');
    Route::get('/account/analytics', AccountAnalytics::class)->middleware('can:analytics.view')->name('account.analytics');
    Route::get('/activity', static function () use ($renderLivewireComponent) {
        $entityType = request('entity_type', 'user');
        $entityId = (int) request('entity_id', auth()->id());

        if ($entityType === 'user') {
            $targetUser = User::findOrFail($entityId);
            $viewer = auth()->user();

            if (!$targetUser->canViewPrivacySection($viewer, 'activity') && $viewer->id !== $targetUser->id && !$viewer->isAdmin()) {
                abort(403, __('profile.activity_private'));
            }
        }

        return $renderLivewireComponent(Common\Friend\ActivityLog::class, [
            'entityType' => $entityType,
            'entityId' => $entityId,
        ]);
    })->name('activity');

    $commonComponents = [
        'friend-requests' => [
            'component' => Common\Friend\FriendList::class,
            'parameters' => [
                'entityType' => 'user',
                'entityId' => static fn(): int => auth()->id(),
            ],
        ],
        'friends' => [
            'component' => Common\Friend\FriendList::class,
            'parameters' => [
                'entityType' => 'user',
                'entityId' => static fn(): int => auth()->id(),
            ],
        ],
        'followers' => [
            'component' => Common\Follow\FollowList::class,
            'parameters' => [],
        ],
    ];

    foreach ($commonComponents as $route => $config) {
        Route::get("/$route", static fn() => $renderLivewireComponent(
            $config['component'],
            $config['parameters'] ?? []
        ))->name(str_replace('-', '.', $route));
    }

    Route::prefix('friends')->name('friend.')->group(function () use ($renderLivewireComponent) {
        $friendComponents = [
            'dashboard' => Common\Friend\Hub::class,
            'export' => Common\Friend\Export::class,
            'finder' => Common\Friend\Finder::class,
            'analytics' => Common\Friend\Analytics::class,
        ];

        foreach ($friendComponents as $route => $componentClass) {
            Route::get("/$route", static fn() => $renderLivewireComponent(
                $componentClass,
                [
                    'entityType' => 'user',
                    'entityId' => static fn(): int => auth()->id(),
                ]
            ))->name($route);
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

    Route::prefix('pets')->name('pet.')->group(function () use ($renderLivewireComponent) {
        $petComponents = [
            'friends' => Common\Friend\FriendList::class,
            'dashboard' => Common\Friend\Hub::class,
            'finder' => Common\Friend\Finder::class,
            'analytics' => Common\Friend\Analytics::class,
            'notifications' => Common\NotificationCenter::class,
            'posts' => Common\PostManager::class,
        ];

        foreach ($petComponents as $route => $componentClass) {
            Route::get("/$route/{pet}", static function (\App\Models\Pet $pet) use ($renderLivewireComponent, $componentClass) {
                return $renderLivewireComponent($componentClass, [
                    'entityType' => 'pet',
                    'entityId' => $pet->getKey(),
                ]);
            })->name($route);
        }

        // Dedicated page for owners to maintain private medical records.
        Route::get('/medical-records/{pet}', Pet\MedicalRecords::class)->name('medical-records');
        Route::get('/{pet}', Pet\PetProfile::class)->name('profile');
    });

    Route::prefix('groups')->name('group.')->group(function () {
        Route::get('/', Group\Management\Index::class)->name('index');
        Route::get('/{group}', Group\Details\Show::class)->name('detail');
        Route::get('/{group}/events', Group\Events\Index::class)->name('events');
    });
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', Admin\Dashboard::class)->name('dashboard');
    Route::get('/users', Admin\ManageUsers::class)->name('users');
    Route::get('/analytics', Admin\Analytics::class)->name('analytics');
    Route::get('/reports', fn () => response()->noContent())->name('reports');
    Route::get('/settings', fn () => response()->noContent())->name('settings');
});

require __DIR__ . '/auth.php';
