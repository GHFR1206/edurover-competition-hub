<?php

namespace App\Http\Middleware;

use App\Models\Arena;
use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

/**
 * Authenticates an arena timing device.
 *
 * Each arena owns a dedicated service account (`arenas.api_user_id`) and one
 * Sanctum token with the `arena:timing` ability. The token therefore can only
 * ever report timings for its own arena, and revoking it takes effect
 * immediately without touching any human account.
 */
class EnsureArenaDevice
{
    public const ABILITY = 'arena:timing';

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Token arena tidak valid.'], 401);
        }

        $token = $user->currentAccessToken();

        // Bearer token only: a browser session must never be able to act as
        // an arena device.
        if (! $token instanceof PersonalAccessToken) {
            return $this->deny('Endpoint ini hanya menerima token perangkat arena.');
        }

        if (! $token->can(self::ABILITY)) {
            return $this->deny('Token tidak memiliki izin arena:timing.');
        }

        $arena = Arena::where('api_user_id', $user->id)->first();

        if (! $arena) {
            return $this->deny('Akun token tidak terhubung ke arena mana pun.');
        }

        if (! $arena->is_active) {
            return $this->deny('Arena sedang nonaktif.');
        }

        // Handy for controllers and logging.
        $request->attributes->set('arena', $arena);
        $request->attributes->set('device_id', $token->name);

        return $next($request);
    }

    private function deny(string $message): Response
    {
        return response()->json(['message' => $message], 403);
    }
}
