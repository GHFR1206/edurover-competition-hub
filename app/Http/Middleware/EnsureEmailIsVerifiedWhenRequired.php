<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Applies the Super Admin "require_email_verification" setting to every
 * authenticated area. When the setting is on, an unverified account is sent
 * to the verification notice instead of the requested page.
 *
 * The verification routes themselves, logout and the profile page stay
 * reachable so the user can still verify, fix their profile, or sign out.
 */
class EnsureEmailIsVerifiedWhenRequired
{
    /** @var array<int, string> */
    private const EXEMPT = [
        'verification.notice',
        'verification.verify',
        'verification.send',
        'logout',
        'profile.edit',
        'profile.update',
        'profile.destroy',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! Setting::flag('require_email_verification')) {
            return $next($request);
        }

        if ($user->hasVerifiedEmail() || $request->routeIs(...self::EXEMPT)) {
            return $next($request);
        }

        return $request->expectsJson()
            ? response()->json(['message' => 'Email belum diverifikasi.'], 403)
            : redirect()->route('verification.notice');
    }
}
