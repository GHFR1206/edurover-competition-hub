<?php

use App\Http\Controllers\Coach\ResultController;
use App\Http\Controllers\Coach\TeamController;
use Illuminate\Support\Facades\Route;

Route::get('/dashboard', [TeamController::class, 'dashboard'])->name('dashboard');
Route::get('/tim', [TeamController::class, 'index'])->name('teams.index');
Route::get('/tim/{team:uuid}', [TeamController::class, 'show'])->name('teams.show');
Route::get('/hasil', [ResultController::class, 'index'])->name('results.index');
Route::get('/hasil/{team:uuid}', [ResultController::class, 'show'])->name('results.show');
