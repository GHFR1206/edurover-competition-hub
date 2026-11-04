<?php

use App\Http\Controllers\Operator\ArenaController;
use Illuminate\Support\Facades\Route;

Route::get('/dashboard', [ArenaController::class, 'dashboard'])->name('dashboard');
Route::get('/arena', [ArenaController::class, 'index'])->name('arenas.index');
Route::get('/arena/{arena:uuid}', [ArenaController::class, 'show'])->name('arenas.show');
Route::patch('/run/{run:uuid}/status', [ArenaController::class, 'updateRunStatus'])->name('runs.status');
