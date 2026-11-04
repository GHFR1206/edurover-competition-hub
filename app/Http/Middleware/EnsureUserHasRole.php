<?php

namespace App\Http\Middleware;

use App\Enums\Role as RoleEnum;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restricts a route to one or more platform roles.
 * Usage: ->middleware('role:event_admin,super_admin')
 */
class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! $user->is_active) {
            abort(403);
        }

        // Super Admin implicitly passes every role gate.
        if ($user->isRole(RoleEnum::SuperAdmin)) {
            return $next($request);
        }

        foreach ($roles as $role) {
            if ($user->isRole(RoleEnum::from($role))) {
                return $next($request);
            }
        }

        abort(403, 'Anda tidak memiliki akses ke halaman ini.');
    }
}
