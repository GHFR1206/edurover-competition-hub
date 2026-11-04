<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Public\LandingController;
use App\Http\Controllers\Public\ResultsController;
use App\Http\Controllers\RoleDashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public routes (no authentication)
|--------------------------------------------------------------------------
*/

Route::get('/', LandingController::class)->name('home');
Route::get('/hasil', [ResultsController::class, 'index'])->name('public.results');
Route::get('/hasil/{category:uuid}', [ResultsController::class, 'show'])->name('public.results.category');

/*
|--------------------------------------------------------------------------
| Authenticated routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/dashboard', RoleDashboardController::class)->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Coach / Sekolah
    Route::middleware('role:coach')
        ->prefix('coach')->name('coach.')
        ->group(base_path('routes/role/coach.php'));

    // Juri
    Route::middleware('role:judge')
        ->prefix('juri')->name('judge.')
        ->group(base_path('routes/role/judge.php'));

    // Operator Arena
    Route::middleware('role:arena_operator')
        ->prefix('operator')->name('operator.')
        ->group(base_path('routes/role/operator.php'));

    // Chief Judge
    Route::middleware('role:chief_judge')
        ->prefix('chief')->name('chief.')
        ->group(base_path('routes/role/chief.php'));
});

require __DIR__.'/auth.php';
