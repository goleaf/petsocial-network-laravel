<?php

use App\Http\Controllers\ProfileController;
use App\Http\Livewire\AdminAnalytics;
use App\Http\Livewire\AdminDashboard;
use App\Http\Livewire\AdminManageUsers;
use App\Http\Livewire\Messages;
use App\Http\Livewire\TagSearch;
use App\Http\Livewire\UserSettings;
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
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/profile/{user}', function (User $user) {
        return view('profile', compact('user'));
    })->name('profile');

    Route::get('/tags', TagSearch::class)->name('tag.search');
    Route::get('/messages', Messages::class)->middleware('auth')->name('messages');
    Route::get('/settings', UserSettings::class)->middleware('auth')->name('settings');


});


Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/dashboard', AdminDashboard::class)->name('admin.dashboard');
    Route::get('/admin/users', AdminManageUsers::class)->name('admin.users');

    Route::get('/admin/dashboard', AdminDashboard::class)->name('admin.dashboard');
    Route::get('/admin/users', AdminManageUsers::class)->name('admin.users');
    Route::get('/admin/analytics', AdminAnalytics::class)->name('admin.analytics');

});

require __DIR__.'/auth.php';
