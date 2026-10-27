<?php

use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Dashboard\EventController as DashboardEventController;
use App\Http\Controllers\Dashboard\UserController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TeamController;
// For EVENT_PAGE
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public localized routes
|--------------------------------------------------------------------------
*/

Route::redirect('/', '/es');

Route::prefix('{locale}')
    ->where(['locale' => 'en|es'])
    ->middleware('locale')
    ->group(function () {
        Route::get('/', [HomeController::class, 'index'])
            ->name('home');

        // Newsletter sign-up form at the bottom of the home page.
        Route::post('/newsletter', [NewsletterController::class, 'store'])
            ->middleware('throttle:6,1')
            ->name('newsletter.store');

        // For event page:

        Route::get('/events', [EventController::class, 'index'])
            ->name('events.index');

        // For events page
        Route::get('/events/{event}', [EventController::class, 'show'])
            ->name('events.show');

        // For team page:
        Route::get('/team', [TeamController::class, 'index'])
            ->name('team.index');

        // For projects page:
        Route::get('/projects', [ProjectController::class, 'index'])
            ->name('projects.index');

        // For project page:
        Route::get('/projects/{project}', [ProjectController::class, 'show'])
            ->name('projects.show');

        // For about page:
        Route::view('/about', 'about')
            ->name('about');

        // For terms and conditions page:
        Route::view('/terms-conditions', 'terms')
            ->name('terms');

        // For statutes page:
        Route::view('/estatutos', 'estatutos')
            ->name('estatutos');

    });

/*
|--------------------------------------------------------------------------
| Authenticated routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('dashboard');

        Route::prefix('dashboard')
            ->name('dashboard.')
            ->group(function () {
                // Every member can see the events list (like the legacy events_list.php).
                Route::get('/events', [DashboardEventController::class, 'index'])
                    ->name('events.index');

                Route::middleware('role:admin,events')->group(function () {
                    Route::get('/users', [UserController::class, 'index'])
                        ->name('users.index');

                    Route::resource('events', DashboardEventController::class)
                        ->except(['index', 'show']);

                    Route::get('/events/{event}/attendees.pdf', [DashboardEventController::class, 'attendeesPdf'])
                        ->name('events.attendees-pdf');
                });
            });
    });

require __DIR__.'/settings.php';
