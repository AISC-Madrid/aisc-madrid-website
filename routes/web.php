<?php

use App\Http\Controllers\TeamController;
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
        Route::view('/', 'home')
            ->name('home');

        Route::view('/events', 'events.index')
            ->name('events.index');

        Route::get('/team', [TeamController::class, 'index'])
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

Route::middleware(['auth', 'verified'])
    ->group(function () {
        Route::view('/dashboard', 'dashboard')
            ->name('dashboard');
});

require __DIR__.'/settings.php';