<?php

use Illuminate\Support\Facades\Route;

/* For EVENT_PAGE */
use App\Http\Controllers\EventController;

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
        Route::view('/', 'home')->name('home');

        //FOR EVENT PAGE:
        //For events page

        Route::get('/events', [EventController::class, 'index'])
            ->name('events.index');

        Route::get('/events/{event}', [EventController::class, 'show'])
            ->name('events.show');

        Route::view('/team', 'team.index')
            ->name('team.index');

        Route::view('/projects', 'projects.index')
            ->name('projects.index');

        Route::view('/about', 'about')
            ->name('about');
    });

    

/*
|--------------------------------------------------------------------------
| Authenticated routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('/dashboard', 'dashboard')
        ->name('dashboard');
});

require __DIR__ . '/settings.php';
