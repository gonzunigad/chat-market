<?php

use App\Http\Controllers\GoogleController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('auth/google', [GoogleController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('calendar', [\App\Http\Controllers\EventController::class, 'index'])->name('calendar');
    Route::post('chat-listings', [\App\Http\Controllers\ChatListingController::class, 'store'])->name('list-chat');
    Route::delete('delete-listing', [\App\Http\Controllers\ChatListingController::class, 'destroy'])->name('delete-listing');
    Route::delete('upgrade-listing', [\App\Http\Controllers\ChatListingController::class, 'upgrade'])->name('upgrade-listing');
    Route::post('take-chat', [\App\Http\Controllers\ChatListingController::class, 'take'])->name('take-chat');

    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');
});
