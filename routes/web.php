<?php

use App\Http\Controllers\ProfileController;
use App\Http\Livewire\AdminAnalytics;
use App\Http\Livewire\AdminDashboard;
use App\Http\Livewire\AdminManageUsers;
use App\Http\Livewire\FriendRequests;
use App\Http\Livewire\Messages;
use App\Http\Livewire\TagSearch;
use App\Http\Livewire\UserSettings;
use App\Http\Livewire\Friends;
use App\Http\Livewire\Followers;
use App\Http\Livewire\PetManagement;
use App\Http\Livewire\PetFriends;
use App\Models\User;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Account management routes
    Route::prefix('account')->name('account.')->group(function () {
        Route::post('/deactivate', [\App\Http\Controllers\AccountController::class, 'deactivate'])->name('deactivate');
        Route::post('/delete', [\App\Http\Controllers\AccountController::class, 'delete'])->name('delete');
        Route::post('/password', [\App\Http\Controllers\AccountController::class, 'updatePassword'])->name('password.update');
    });
    
    // Two-factor authentication routes
    Route::prefix('two-factor')->name('two-factor.')->group(function () {
        Route::get('/enable', [\App\Http\Controllers\TwoFactorAuthController::class, 'enable'])->name('enable');
        Route::post('/confirm', [\App\Http\Controllers\TwoFactorAuthController::class, 'confirm'])->name('confirm');
        Route::post('/disable', [\App\Http\Controllers\TwoFactorAuthController::class, 'disable'])->name('disable');
        Route::get('/challenge', [\App\Http\Controllers\TwoFactorAuthController::class, 'challenge'])->name('challenge');
        Route::post('/verify', [\App\Http\Controllers\TwoFactorAuthController::class, 'verify'])->name('verify');
    });

    Route::get('/profile/{user}', function (User $user) {
        if ($user->profile_visibility === 'private' && $user->id !== auth()->id()) {
            abort(403, 'This profile is private.');
        }
        if ($user->profile_visibility === 'friends' && !$user->friends->contains(auth()->id()) && $user->id !== auth()->id()) {
            abort(403, 'This profile is visible to friends only.');
        }
        return view('profile', compact('user'));
    })->name('profile');

    Route::get('/tags', TagSearch::class)->name('tag.search');
    Route::get('/messages', Messages::class)->name('messages');
    Route::get('/settings', UserSettings::class)->name('settings');
    Route::get('/friend-requests', FriendRequests::class)->name('friend.requests');

    // Livewire components for friends/followers display
    Route::get('/friends', Friends::class)->name('friends');
    Route::get('/followers', Followers::class)->name('followers');
    Route::get('/friend-activity', App\Http\Livewire\FriendActivity::class)->name('friend.activity');
    Route::get('/friend-dashboard', App\Http\Livewire\FriendDashboard::class)->name('friend.dashboard');
    
    // Friendship routes
    Route::prefix('friendships')->name('friendships.')->group(function () {
        Route::get('/', [\App\Http\Controllers\FriendshipController::class, 'index'])->name('index');
        Route::post('/{user}/request', [\App\Http\Controllers\FriendshipController::class, 'sendRequest'])->name('request');
        Route::post('/accept/{friendship}', [\App\Http\Controllers\FriendshipController::class, 'acceptRequest'])->name('accept');
        Route::post('/decline/{friendship}', [\App\Http\Controllers\FriendshipController::class, 'declineRequest'])->name('decline');
        Route::delete('/{user}', [\App\Http\Controllers\FriendshipController::class, 'removeFriend'])->name('remove');
        Route::post('/{user}/categorize', [\App\Http\Controllers\FriendshipController::class, 'categorize'])->name('categorize');
        Route::post('/{user}/block', [\App\Http\Controllers\FriendshipController::class, 'blockUser'])->name('block');
        Route::post('/{user}/unblock', [\App\Http\Controllers\FriendshipController::class, 'unblockUser'])->name('unblock');
        Route::get('/blocked', [\App\Http\Controllers\FriendshipController::class, 'blockedUsers'])->name('blocked');
    });
    
    // Follow routes
    Route::prefix('follows')->name('follows.')->group(function () {
        Route::get('/', [\App\Http\Controllers\FollowController::class, 'index'])->name('index');
        Route::post('/{user}', [\App\Http\Controllers\FollowController::class, 'follow'])->name('follow');
        Route::delete('/{user}', [\App\Http\Controllers\FollowController::class, 'unfollow'])->name('unfollow');
        Route::post('/{user}/notifications', [\App\Http\Controllers\FollowController::class, 'toggleNotifications'])->name('toggle-notifications');
        Route::get('/recommendations', [\App\Http\Controllers\FollowController::class, 'recommendations'])->name('recommendations');
    });
    
    // User profile routes for viewing followers/following
    Route::get('/users/{user}/followers', [\App\Http\Controllers\FollowController::class, 'followers'])->name('users.followers');
    Route::get('/users/{user}/following', [\App\Http\Controllers\FollowController::class, 'following'])->name('users.following');


    Route::get('/pets', PetManagement::class)->name('pets');
    Route::get('/pet-friends/{petId}', PetFriends::class)->name('pet.friends');

});


Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/dashboard', AdminDashboard::class)->name('admin.dashboard');
    Route::get('/admin/users', AdminManageUsers::class)->name('admin.users');

    Route::get('/admin/dashboard', AdminDashboard::class)->name('admin.dashboard');
    Route::get('/admin/users', AdminManageUsers::class)->name('admin.users');
    Route::get('/admin/analytics', AdminAnalytics::class)->name('admin.analytics');

});

require __DIR__.'/auth.php';
