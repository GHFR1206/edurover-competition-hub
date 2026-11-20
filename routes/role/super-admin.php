<?php

use App\Http\Controllers\SuperAdmin\AuditController;
use App\Http\Controllers\SuperAdmin\DashboardController;
use App\Http\Controllers\SuperAdmin\SettingController;
use App\Http\Controllers\SuperAdmin\UserController;
use App\Http\Controllers\SuperAdmin\VerificationController;
use Illuminate\Support\Facades\Route;

Route::get('/dashboard', DashboardController::class)->name('dashboard');
Route::get('/verifikasi', [VerificationController::class, 'index'])->name('verification.index');
Route::get('/verifikasi/{registration:uuid}', [VerificationController::class, 'show'])->name('verification.show');
Route::patch('/verifikasi/{registration:uuid}/setujui', [VerificationController::class, 'approve'])->name('verification.approve');
Route::patch('/verifikasi/{registration:uuid}/tolak', [VerificationController::class, 'reject'])->name('verification.reject');
Route::patch('/verifikasi/{registration:uuid}/revisi', [VerificationController::class, 'requestRevision'])->name('verification.revision');
Route::get('/pengguna', [UserController::class, 'index'])->name('users.index');
Route::post('/pengguna', [UserController::class, 'store'])->name('users.store');
Route::patch('/pengguna/{user}/toggle', [UserController::class, 'toggleActive'])->name('users.toggle');
Route::get('/audit', AuditController::class)->name('audit.index');
Route::get('/pengaturan', [SettingController::class, 'index'])->name('settings.index');
Route::patch('/pengaturan', [SettingController::class, 'update'])->name('settings.update');
