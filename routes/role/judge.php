<?php

use App\Http\Controllers\Judge\ScoringController;
use Illuminate\Support\Facades\Route;

Route::get('/dashboard', [ScoringController::class, 'dashboard'])->name('dashboard');
Route::get('/tugas', [ScoringController::class, 'index'])->name('tasks.index');
Route::get('/run/{run:uuid}/nilai', [ScoringController::class, 'edit'])->name('scores.edit');
Route::post('/run/{run:uuid}/nilai', [ScoringController::class, 'store'])->name('scores.store');
Route::patch('/nilai/{score:uuid}/ajukan', [ScoringController::class, 'submit'])->name('scores.submit');
Route::get('/skor-saya', [ScoringController::class, 'mine'])->name('scores.mine');
