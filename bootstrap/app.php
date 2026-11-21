<?php

use App\Http\Middleware\EnsureArenaDevice;
use App\Http\Middleware\EnsureEmailIsVerifiedWhenRequired;
use App\Http\Middleware\EnsureUserHasRole;
use App\Http\Middleware\EnsureUserIsActive;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => EnsureUserHasRole::class,
            'active' => EnsureUserIsActive::class,
            'verified.setting' => EnsureEmailIsVerifiedWhenRequired::class,
            'arena.device' => EnsureArenaDevice::class,
        ]);

        $middleware->web(append: [
            EnsureUserIsActive::class,
        ]);

        // Arena devices authenticate with a Sanctum bearer token. No session,
        // no CSRF and no stateful SPA cookie is involved, so the default
        // `api` group (throttling + bindings) is enough.
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // The device API is machine-to-machine: always answer with JSON, even
        // when the client forgets `Accept: application/json` (otherwise an
        // unauthenticated call would redirect to the login page).
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
