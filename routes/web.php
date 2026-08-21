<?php

use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ExperienceController;
use App\Http\Controllers\Admin\LoginController;
use App\Http\Controllers\Admin\MessageController;
use App\Http\Controllers\Admin\PrincipleController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\SkillGroupController;
use App\Http\Controllers\Admin\SocialController;
use App\Http\Controllers\Admin\StatusItemController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\TrackController;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Route;
use Illuminate\View\Middleware\ShareErrorsFromSession;

/*
|--------------------------------------------------------------------------
| Public site
|--------------------------------------------------------------------------
*/

Route::get('/', HomeController::class)->name('home');

/*
 * Analytics beacons.
 *
 * Deliberately stateless. Running these through the session middleware cost
 * three extra queries per beacon and, worse, took a lock on the session row --
 * so concurrent beacons from one browser queued up behind each other. There is
 * nothing per-user to remember here, so the whole session stack comes off.
 *
 * CSRF goes with it; TrackController checks the request origin instead.
 */
Route::post('/track', [TrackController::class, 'store'])
    ->withoutMiddleware([
        EncryptCookies::class,
        AddQueuedCookiesToResponse::class,
        StartSession::class,
        ShareErrorsFromSession::class,
        ValidateCsrfToken::class,
    ])
    ->middleware('throttle:30,1')
    ->name('track');

// Five messages a minute per IP is plenty for a portfolio inbox.
Route::post('/contact', [ContactController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('contact.store');

/*
|--------------------------------------------------------------------------
| Admin
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->name('admin.')->group(function () {

    Route::middleware('guest')->group(function () {
        Route::get('login', [LoginController::class, 'create'])->name('login');
        // Throttled separately from the contact form: this one guards a password.
        Route::post('login', [LoginController::class, 'store'])
            ->middleware('throttle:10,1')
            ->name('login.store');
    });

    Route::middleware('auth')->group(function () {
        Route::post('logout', [LoginController::class, 'destroy'])->name('logout');

        Route::get('/', DashboardController::class)->name('dashboard');
        Route::get('analytics', AnalyticsController::class)->name('analytics');

        Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');

        Route::resource('projects', ProjectController::class)->except('show');
        Route::resource('experiences', ExperienceController::class)->except('show');
        Route::resource('skills', SkillGroupController::class)->except('show');
        Route::resource('socials', SocialController::class)->except('show');
        Route::resource('status', StatusItemController::class)->except('show');
        Route::resource('principles', PrincipleController::class)->except('show');

        Route::get('messages', [MessageController::class, 'index'])->name('messages.index');
        Route::get('messages/{message}', [MessageController::class, 'show'])->name('messages.show');
        Route::delete('messages/{message}', [MessageController::class, 'destroy'])->name('messages.destroy');
    });
});
