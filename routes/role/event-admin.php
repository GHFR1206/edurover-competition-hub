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
Route::patch('/setup/kategori/{category:uuid}', [SetupController::class, 'updateCategory'])->name('setup.category');
Route::get('/staff', StaffController::class)->name('staff.index');
Route::post('/staff', [StaffController::class, 'store'])->name('staff.store');
Route::patch('/staff/{assignment:uuid}/toggle', [StaffController::class, 'toggle'])->name('staff.toggle');
Route::delete('/staff/{assignment:uuid}', [StaffController::class, 'destroy'])->name('staff.destroy');
Route::get('/jadwal', [ScheduleController::class, 'index'])->name('schedule.index');
Route::post('/jadwal', [ScheduleController::class, 'store'])->name('schedule.store');
Route::patch('/jadwal/{run:uuid}', [ScheduleController::class, 'update'])->name('schedule.update');
Route::delete('/jadwal/{run:uuid}', [ScheduleController::class, 'destroy'])->name('schedule.destroy');
Route::get('/laporan', [ReportController::class, 'index'])->name('reports.index');
Route::get('/laporan/ekspor/klasemen', [ReportController::class, 'exportStandings'])->name('reports.export.standings');
Route::get('/laporan/ekspor/partisipasi', [ReportController::class, 'exportParticipation'])->name('reports.export.participation');
