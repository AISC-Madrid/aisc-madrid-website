<?php

use App\Http\Controllers\EventController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TeamController;
/* For EVENT_PAGE */
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

        // For event page:

        Route::get('/events', [EventController::class, 'index'])
            ->name('events.index');

        // For events page
        Route::get('/events/{event}', [EventController::class, 'show'])
            ->name('events.show');

        
        //For team page:
        Route::get('/team', [TeamController::class, 'index'])
            ->name('team.index');

        //For projects page:
        Route::get('/projects', [ProjectController::class, 'index'])
            ->name('projects.index');

        //For project page:
        Route::get('/projects/{project}', [ProjectController::class, 'show'])
            ->name('projects.show');
        
        //For about page:
        Route::view('/about', 'about')
            ->name('about');

    });

/*
|--------------------------------------------------------------------------
| Authenticated routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])
    ->group(function () {
        Route::view('/dashboard', 'dashboard')
            ->name('dashboard');
    });

require __DIR__.'/settings.php';
