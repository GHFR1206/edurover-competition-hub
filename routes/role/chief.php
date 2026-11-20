<?php

use App\Http\Controllers\Chief\AppealController;
use App\Http\Controllers\Chief\ApprovalController;
use App\Http\Controllers\Chief\PublicationController;
use Illuminate\Support\Facades\Route;

Route::get('/dashboard', [ApprovalController::class, 'dashboard'])->name('dashboard');
Route::get('/verifikasi', [ApprovalController::class, 'index'])->name('verification.index');
Route::get('/tinjau', [ApprovalController::class, 'judging'])->name('judging.index');
Route::get('/koreksi', [ApprovalController::class, 'correction'])->name('correction.index');
Route::patch('/koreksi/{score:uuid}', [ApprovalController::class, 'correct'])->name('correction.store');
Route::get('/verifikasi/{score:uuid}', [ApprovalController::class, 'show'])->name('verification.show');
Route::patch('/verifikasi/{score:uuid}/setujui', [ApprovalController::class, 'approve'])->name('verification.approve');
Route::patch('/verifikasi/{score:uuid}/tolak', [ApprovalController::class, 'reject'])->name('verification.reject');
Route::patch('/verifikasi/{score:uuid}/buka', [ApprovalController::class, 'reopen'])->name('verification.reopen');
Route::get('/publikasi', [PublicationController::class, 'index'])->name('publication.index');
Route::post('/publikasi/{category:uuid}', [PublicationController::class, 'publish'])->name('publication.publish');
Route::post('/publikasi/{category:uuid}/sertifikat', [PublicationController::class, 'issueCertificates'])->name('publication.certificates');
Route::get('/banding', [AppealController::class, 'index'])->name('appeals.index');
Route::patch('/banding/{appeal:uuid}', [AppealController::class, 'resolve'])->name('appeals.resolve');
