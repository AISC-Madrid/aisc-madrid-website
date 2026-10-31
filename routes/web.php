<?php

use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Dashboard\EventController as DashboardEventController;
use App\Http\Controllers\Dashboard\UserController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\NewsletterUnsubscribeController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TeamController;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public localized routes
|--------------------------------------------------------------------------
*/

Route::redirect('/', '/es');

// Unsubscribe links in emails sent by the legacy PHP site.
Route::get('/processing/unsubscribe.php', [NewsletterUnsubscribeController::class, 'legacy'])
    ->name('newsletter.unsubscribe.legacy');

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

        // Newsletter unsubscribe by token: GET only asks for confirmation (link scanners open
        // every URL), POST unsubscribes, and one-click is for mail clients (RFC 8058, no CSRF).
        Route::prefix('/newsletter/unsubscribe/{submission:unsubscribe_token}')
            ->name('newsletter.unsubscribe.')
            ->middleware('throttle:30,1')
            ->group(function () {
                Route::get('/', [NewsletterUnsubscribeController::class, 'show'])
                    ->missing(fn (Request $request) => NewsletterUnsubscribeController::invalidLink($request))
                    ->name('show');

                Route::post('/', [NewsletterUnsubscribeController::class, 'store'])
                    ->missing(fn (Request $request) => NewsletterUnsubscribeController::invalidLink($request))
                    ->name('store');

                Route::post('/one-click', [NewsletterUnsubscribeController::class, 'oneClick'])
                    ->withoutMiddleware(PreventRequestForgery::class)
                    ->missing(fn () => response()->noContent())
                    ->name('one-click');
            });

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
