<?php

use App\Http\Controllers\CertificateController;
use App\Http\Controllers\EvidenceController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Public\ArchiveController;
use App\Http\Controllers\Public\EventController;
use App\Http\Controllers\Public\LandingController;
use App\Http\Controllers\Public\LeaderboardController;
use App\Http\Controllers\Public\ResultsController;
use App\Http\Controllers\Public\ScheduleController;
use App\Http\Controllers\RoleDashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public routes (no authentication)
|--------------------------------------------------------------------------
*/

Route::get('/', LandingController::class)->name('home');
Route::get('/event', EventController::class)->name('public.events');
Route::get('/jadwal', ScheduleController::class)->name('public.schedule');
Route::get('/leaderboard', LeaderboardController::class)->name('public.leaderboard');
Route::get('/hasil', [ResultsController::class, 'index'])->name('public.results');
Route::get('/hasil/{category:uuid}', [ResultsController::class, 'show'])->name('public.results.category');
Route::get('/arsip', ArchiveController::class)->name('public.archive');

// Public certificate verification by opaque token.
Route::get('/sertifikat/verifikasi/{token}', [CertificateController::class, 'verify'])->name('certificates.verify');

/*
|--------------------------------------------------------------------------
| Authenticated routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/dashboard', RoleDashboardController::class)->name('dashboard');

    // Private evidence download (authorised users only).
    Route::get('/bukti/{evidence:uuid}', [EvidenceController::class, 'show'])->name('evidence.show');

    // Certificate viewing (own team or staff).
    Route::get('/sertifikat/{certificate:uuid}', [CertificateController::class, 'show'])->name('certificates.show');

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

    // Event Admin
    Route::middleware('role:event_admin')
        ->prefix('event-admin')->name('eventadmin.')
        ->group(base_path('routes/role/event-admin.php'));

    // Super Admin
    Route::middleware('role:super_admin')
        ->prefix('super-admin')->name('superadmin.')
        ->group(base_path('routes/role/super-admin.php'));
});

require __DIR__.'/auth.php';
