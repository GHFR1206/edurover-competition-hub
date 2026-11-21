<?php

use App\Http\Controllers\Api\ArenaTimingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Arena device API (v1)
|--------------------------------------------------------------------------
| Authenticated with a Sanctum bearer token that belongs to the arena's
| service account and carries the `arena:timing` ability. There is no
| session, no CSRF and no human user involved.
|
| Every endpoint is throttled; timing payloads are idempotent so a device
| that retries after a network drop never corrupts an official result.
*/

Route::prefix('v1')->middleware(['auth:sanctum', 'arena.device', 'throttle:120,1'])->group(function () {
    // Which run is currently expected on this arena.
    Route::get('/arena/current-run', [ArenaTimingController::class, 'currentRun']);

    // Start / finish / laps for one run.
    Route::post('/runs/{run:uuid}/start', [ArenaTimingController::class, 'start']);
    Route::post('/runs/{run:uuid}/laps', [ArenaTimingController::class, 'laps']);
    Route::post('/runs/{run:uuid}/finish', [ArenaTimingController::class, 'finish']);
    Route::post('/runs/{run:uuid}/timing', [ArenaTimingController::class, 'timing']);

    // Read back what the server stored (useful for device reconciliation).
    Route::get('/runs/{run:uuid}/timing', [ArenaTimingController::class, 'show']);
});
