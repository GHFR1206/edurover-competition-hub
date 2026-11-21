<?php

use App\Http\Controllers\Coach\AppealController;
use App\Http\Controllers\Coach\ResultController;
use App\Http\Controllers\Coach\TeamController;
use Illuminate\Support\Facades\Route;

Route::get('/dashboard', [TeamController::class, 'dashboard'])->name('dashboard');
Route::get('/tim', [TeamController::class, 'index'])->name('teams.index');
Route::get('/tim/baru', [TeamController::class, 'create'])->name('teams.create');
Route::post('/tim', [TeamController::class, 'store'])->name('teams.store');
Route::get('/tim/{team:uuid}', [TeamController::class, 'show'])->name('teams.show');
Route::post('/tim/{team:uuid}/ajukan', [TeamController::class, 'submit'])->name('teams.submit');
Route::post('/tim/{team:uuid}/anggota', [TeamController::class, 'storeParticipant'])->name('teams.participants.store');
Route::delete('/tim/{team:uuid}/anggota/{participant:uuid}', [TeamController::class, 'destroyParticipant'])->name('teams.participants.destroy');
Route::get('/hasil', [ResultController::class, 'index'])->name('results.index');
Route::get('/hasil/{team:uuid}', [ResultController::class, 'show'])->name('results.show');

Route::get('/banding', [AppealController::class, 'index'])->name('appeals.index');
Route::post('/banding', [AppealController::class, 'store'])->name('appeals.store');
