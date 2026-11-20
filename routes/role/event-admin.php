<?php

use App\Http\Controllers\EventAdmin\DashboardController;
use App\Http\Controllers\EventAdmin\EventController;
use App\Http\Controllers\EventAdmin\ReportController;
use App\Http\Controllers\EventAdmin\ScheduleController;
use App\Http\Controllers\EventAdmin\SetupController;
use App\Http\Controllers\EventAdmin\StaffController;
use Illuminate\Support\Facades\Route;

Route::get('/dashboard', DashboardController::class)->name('dashboard');
Route::get('/event', [EventController::class, 'index'])->name('events.index');
Route::post('/event', [EventController::class, 'store'])->name('events.store');
Route::patch('/event/{event:uuid}/advance', [EventController::class, 'advance'])->name('events.advance');
Route::get('/setup', [SetupController::class, 'index'])->name('setup.index');
Route::get('/staff', StaffController::class)->name('staff.index');
Route::get('/jadwal', [ScheduleController::class, 'index'])->name('schedule.index');
Route::post('/jadwal', [ScheduleController::class, 'store'])->name('schedule.store');
Route::get('/laporan', ReportController::class)->name('reports.index');
