<?php

use App\Http\Controllers\Operator\ArenaController;
use App\Http\Controllers\Operator\IncidentController;
use Illuminate\Support\Facades\Route;

Route::get('/dashboard', [ArenaController::class, 'dashboard'])->name('dashboard');
Route::get('/arena', [ArenaController::class, 'index'])->name('arenas.index');
Route::get('/arena/{arena:uuid}', [ArenaController::class, 'show'])->name('arenas.show');
Route::patch('/run/{run:uuid}/status', [ArenaController::class, 'updateRunStatus'])->name('runs.status');

Route::get('/insiden', [IncidentController::class, 'index'])->name('incidents.index');
Route::post('/insiden', [IncidentController::class, 'store'])->name('incidents.store');
Route::patch('/insiden/{incident:uuid}', [IncidentController::class, 'update'])->name('incidents.update');
